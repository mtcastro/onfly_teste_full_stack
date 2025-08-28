<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderStatusRequest;
use App\Models\TravelOrder;
use App\Notifications\TravelOrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TravelOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = TravelOrder::query();
        // Usuário comum só vê suas ordens
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }
        // Filtros: status, destino, período
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($id = $request->query('id')) {
            $query->where('id', $id);
        }
        if ($dest = $request->query('destination')) {
            $query->where('destination', 'like', "%$dest%");
        }
        // Período por datas de viagem
        if ($from = $request->query('from')) {
            $query->whereDate('departure_date', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('return_date', '<=', $to);
        }

        return $this->success($query->orderByDesc('id')->paginate($request->query('per_page', 5)) );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTravelOrderRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();
        $data['user_id'] = $user->id;
        $data['status'] = 'requested';

        try{
            DB::beginTransaction();
            $order = TravelOrder::create($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Erro ao criar ordem de viagem.', 500);
        }

        return $this->success($order, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = TravelOrder::findOrFail($id);
        $this->authorize('view', $order);
        return $this->success($order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(UpdateTravelOrderStatusRequest $request, $id)
    {
        $order = TravelOrder::findOrFail($id);
        $this->authorize('updateStatus', $order);
        $newStatus = $request->validated()['status'];
        // Regra: só permite cancelar se ainda não foi approved
        if ($newStatus === 'canceled' && $order->status === 'approved') {
            return $this->fail([
                'message' => 'Não é possível cancelar um pedido já approved.'
            ], 422);
        }
        $order->status = $newStatus;

        try {
            DB::beginTransaction();
            $order->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Erro ao atualizar status da ordem de viagem.', 500);
        }

        // Notificar solicitante
        $order->user->notify(new TravelOrderStatusChanged($order));
        return $this->success($order);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = TravelOrder::findOrFail($id);
        $this->authorize('delete', $order);
        try {
            DB::beginTransaction();
            $order->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Erro ao deletar ordem de viagem.', 500);
        }
        return $this->success([ 'message' => 'Ordem de viagem deletada com sucesso.' ]);
    }
}
