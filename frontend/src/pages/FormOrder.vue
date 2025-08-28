<template>
  <q-page padding>
    <q-form
      @submit="onSubmit"
      class="q-col-gutter-sm"
    >
      <div class="q-toolbar">
        <span class="text-h6">{{ form.id ? 'Edit Travel Order' : 'Create Travel Order' }}</span>
      </div>

      <q-input
        disable
        outlined
        v-model="form.requester_name"
        label="Requester Name"
        lazy-rules
        :rules="[val => !!val || 'Please enter the requester name', formErrors.requester_name || true]"
      />

      <q-input
        outlined
        v-model="form.destination"
        label="Destination"
        lazy-rules
        :rules="[val => !!val || 'Please enter a destination', formErrors.destination || true]"
      />

      <div class="row q-col-gutter-sm">
        <q-input
          outlined
          v-model="form.departure_date"
          label="Departure Date"
          type="date"
          class="col-md-6"
          lazy-rules
          :rules="[val => !!val || 'Please enter a departure date', formErrors.departure_date || true]"
        />

        <q-input
          outlined
          v-model="form.return_date"
          label="Return Date"
          type="date"
          class="col-md-6"
          lazy-rules
          :min="form.departure_date"
          :disable="!form.departure_date || form.departure_date == ''"
          :rules="[val => !!val || 'Please enter a return date', formErrors.return_date || true]"
        />
      </div>

      <div class="q-gutter-sm q-mt-md">
        <q-btn
          type="submit"
          color="primary"
          label="Save"
          class="float-right"
          icon="save"
        />
        <q-btn
          type="reset"
          color="white"
          label="Cancel"
          class="float-right"
          text-color="primary"
          icon="cancel"
          :to="{ name: 'home' }"
        />
      </div>
    </q-form>
  </q-page>
</template>

<script>
import { defineComponent, ref, onBeforeMount } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import ErrorService from 'src/services/errorService'
import UserService from 'src/services/userService.js'

export default defineComponent({
  name: 'FormOrders',

  setup () {
    const $q = useQuasar()
    const $router = useRouter()
    const formErrors = ref({})
    const form = ref({
      requester_name: '',
      destination: '',
      departure_date: '',
      return_date: ''
    })

    onBeforeMount(async () => {
      const user = await UserService.get()
      form.value.requester_name = user.name
    })

    const onSubmit = async () => {
      try {
        await api.post('/orders', form.value)
        $q.notify({
          color: 'positive',
          message: 'Tavel Order created successfully',
          icon: 'check'
        })
        $router.push({ name: 'home' })
      } catch (error) {
        const status = error.response.status
        const data = error.response.data || {}

        if ((status === 422) || (data.status && data.status === 'fail')) {
          formErrors.value = data.data || {}
        }

        $q.notify({
          color: 'negative',
          message: 'Error creating order',
          icon: 'warning'
        })
        ErrorService.handleFormError(error, $router, $q)
      }
    }

    return {
      form,
      formErrors,
      onSubmit
    }
  }
})
</script>
