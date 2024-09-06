import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-select-and-create', IndexField)
  app.component('detail-select-and-create', DetailField)
  app.component('form-select-and-create', FormField)
})
