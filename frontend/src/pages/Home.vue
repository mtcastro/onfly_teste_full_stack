<template>
  <q-page padding>
    <!-- Filtro e Busca -->
    <div class="row q-col-gutter-md items-center q-mb-md">
      <!-- Busca por ID -->
      <div class="col-4">
        <q-input
          v-model="searchId"
          label="Search by ID"
          type="number"
          outlined
          dense
          clearable
          @keyup.enter="applyFilters"
        >
          <template v-slot:append>
            <q-btn flat round icon="search" @click="applyFilters" />
          </template>
        </q-input>
      </div>

      <!-- Filtro por Status -->
      <div class="col-4">
        <q-select
          v-model="filterStatus"
          :options="statusOptions"
          label="Search by Status"
          outlined
          dense
          clearable
          @update:model-value="applyFilters"
        />
      </div>

      <!-- Botão limpar filtros -->
      <div class="col-auto">
        <q-btn color="primary" flat label="Clear" @click="resetFilters" />
      </div>
    </div>

    <q-table
      title="Order list"
      :rows="orders"
      :columns="columns"
      row-key="name"
      v-model:pagination="pagination"
      @request="handleRequest"
      :rows-per-page-options="[5,10,20]"
      v-if="user"
    >
      <template v-slot:top>
        <q-toolbar>
          <span class="text-h5">Order list</span>
          <q-space/>
          <q-btn
            v-if="user.role == 'user'"
            color="primary"
            label="Add order"
            icon="add"
            :to="{ name: 'formOrder' }"
          />
        </q-toolbar>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="q-gutter-sm">
          <q-btn
            v-if="user.role == 'user' && props.row.status === 'requested'"
            color="negative"
            dense
            flat
            round
            icon="delete"
            @click="removeOrder(props.row.id)"
          />
          <q-btn
            v-if="user.role == 'admin' && props.row.status === 'requested'"
            color="negative"
            dense
            flat
            round
            icon="cancel"
            @click="cancelOrder(props.row.id)"
          />

          <q-btn
            v-if="user.role == 'admin' && props.row.status === 'requested'"
            color="positive"
            dense
            flat
            round
            icon="check"
            @click="approveOrder(props.row.id)"
          />
        </q-td>
      </template>

    </q-table>
  </q-page>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue'
import { api } from 'boot/axios'
import { useQuasar, date } from 'quasar'
import { useRouter } from 'vue-router'
import ErrorService from 'src/services/errorService'
import UserService from 'src/services/userService.js'

export default defineComponent({
  name: 'homePage',
  setup () {
    const orders = ref([])
    const searchId = ref('')
    const filterStatus = ref(null)
    const user = ref(null)

    const statusOptions = [
      { label: 'Requested', value: 'requested' },
      { label: 'Approved', value: 'approved' },
      { label: 'Canceled', value: 'canceled' }
    ]
    const columns = [
      { name: 'id', label: 'ID', field: 'id', sortable: true, align: 'left' },
      { name: 'requester_name', label: 'Requester', field: 'requester_name', sortable: true, align: 'left' },
      { name: 'destination', label: 'Destination', field: 'destination', sortable: true, align: 'left' },
      { name: 'departure_date', label: 'Departure', field: 'departure_date', sortable: true, align: 'left', format: val => date.formatDate(val, 'DD/MM/YYYY') },
      { name: 'return_date', label: 'Return', field: 'return_date', sortable: true, align: 'left', format: val => date.formatDate(val, 'DD/MM/YYYY') },
      { name: 'status', label: 'Status', field: 'status', sortable: true, align: 'left' },
      { name: 'actions', label: 'Actions', field: 'actions', align: 'left' }
    ]

    const pagination = ref({
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 0
    })

    const $q = useQuasar()
    const $router = useRouter()

    onMounted(async () => {
      user.value = await UserService.get()
      const query = $router.currentRoute.value.query
      if (query.id) {
        searchId.value = query.id
      }
      if (query.status) {
        filterStatus.value = query.status
      }

      fetchOrders(query.page || 1)
    })

    const fetchOrders = async (page = 1) => {
      try {
        const params = {
          per_page: pagination.value.rowsPerPage,
          page: page
        }
        if (searchId.value) {
          params.id = searchId.value
        }
        if (filterStatus.value) {
          params.status = filterStatus.value
        }
        let { data } = await api.get('orders', { params })
        data = data.data
        pagination.value.rowsNumber = data.total
        pagination.value.page = data.current_page
        pagination.value.rowsPerPage = data.per_page

        orders.value = data.data
        console.log(data)
      } catch (error) {
        ErrorService.handlePageError(error, $router, $q)
      }
    }

    const removeOrder = async (id) => {
      try {
        $q.dialog({
          title: 'Confirm',
          message: 'Are you sure to delete this order?',
          cancel: true,
          persistent: true
        }).onOk(async () => {
          await api.delete(`orders/${id}`)
          $q.notify({ message: 'Order removed successfully', icon: 'check', color: 'positive' })
          await fetchOrders()
        })
      } catch (error) {
        $q.notify({ message: 'Error removing order', icon: 'times', color: 'negative' })
      }
    }

    const applyFilters = () => {
      const query = {}
      if (searchId.value) {
        query.id = searchId.value
      }
      if (filterStatus.value) {
        query.status = filterStatus.value.value
      }

      $router.push({ query })
      fetchOrders()
    }

    const resetFilters = () => {
      searchId.value = ''
      filterStatus.value = null
      $router.push({ query: {} })
      fetchOrders()
    }

    const cancelOrder = async (id) => {
      try {
        $q.dialog({
          title: 'Confirm',
          message: 'Are you sure to cancel this order?',
          cancel: true,
          persistent: true
        }).onOk(async () => {
          await api.put(`orders/${id}/cancel`, { status: 'canceled' })
          $q.notify({ message: 'Order canceled successfully', icon: 'check', color: 'positive' })
          await fetchOrders()
        })
      } catch (error) {
        $q.notify({ message: 'Error canceling order', icon: 'times', color: 'negative' })
      }
    }

    const approveOrder = async (id) => {
      try {
        $q.dialog({
          title: 'Confirm',
          message: 'Are you sure to approve this order?',
          cancel: true,
          persistent: true
        }).onOk(async () => {
          await api.put(`orders/${id}/approve`, { status: 'approved' })
          $q.notify({ message: 'Order approved successfully', icon: 'check', color: 'positive' })
          await fetchOrders()
        })
      } catch (error) {
        $q.notify({ message: 'Error approving order', icon: 'times', color: 'negative' })
      }
    }

    const handleRequest = (props) => {
      if (props.pagination.page !== pagination.value.page || props.pagination.rowsPerPage !== pagination.value.rowsPerPage) {
        pagination.value = props.pagination
        fetchOrders(props.pagination.page)
      }
    }

    return {
      orders,
      columns,
      statusOptions,
      searchId,
      filterStatus,
      user,
      pagination,
      removeOrder,
      applyFilters,
      resetFilters,
      cancelOrder,
      approveOrder,
      handleRequest
    }
  }
})
</script>
