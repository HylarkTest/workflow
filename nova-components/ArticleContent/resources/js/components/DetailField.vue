<template>
  <PanelItem :index="index" :field="field">
    <template #value>
      <SupportArticleContent
          style="color: #333c47; word-break: initial; font-weight: 400;"
          :content="field.value"
      ></SupportArticleContent>
    </template>
  </PanelItem>
</template>

<script>

import SupportArticleContent from '../../../../../frontend/src/components/supportSite/SupportArticleContent.vue';
export default {
  components: {
    SupportArticleContent,
  },
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

  data () {
    return {
    }
  }
}
</script>

<style>
:root {
    --hl-primary-color-600: #341aff;
    --hl-primary-color-500: #6257ff;
    --hl-primary-color-400: #8a8aff;
    --hl-cm-color-600: #516176;
}

</style>
