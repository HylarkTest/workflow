import IndexField from './components/IndexField';
import DetailField from './components/DetailField';
import FormField from './components/FormField';

import VueDOMPurifyHTML from '../../../../frontend/src/core/plugins/vueDompurifyHtml.js';

Nova.booting((app, store) => {
  app.component('index-article-content', IndexField);
  app.component('detail-article-content', DetailField);
  app.component('form-article-content', FormField);
  app.use(VueDOMPurifyHTML);
})
