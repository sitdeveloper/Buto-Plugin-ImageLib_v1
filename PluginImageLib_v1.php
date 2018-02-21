<?php
/**
 * Create a slider by upload images in browser. Images and text from a single folder.
 * Set up it as a page plugin and sign in as webmaster or webadmin. Widget examples is showing up at the backend.
 */
class PluginImageLib_v1{
  /**
   * Vars.
   */
  private $settings = null;
  /**
   * Constructor.
   */
  function __construct($buto) {
    if($buto){
      wfPlugin::includeonce('wf/yml');
      wfPlugin::includeonce('wf/array');
      wfPlugin::enable('wf/form_v2');
    }
  }
  /**
   * Init method.
   */
  private function init_page(){
     wfArray::set($GLOBALS, 'sys/layout_path', '/plugin/image/lib_v1/layout');
    if(!wfUser::hasRole("webmaster") && !wfUser::hasRole("webadmin")){
      exit('Role webmaster or webadmin is required!');
    }
    $this->settings = new PluginWfArray(wfArray::get($GLOBALS, 'sys/settings/plugin_modules/'.wfArray::get($GLOBALS, 'sys/class').'/settings'));
  }
  /**
   * Start page.
   */
  public function page_start(){
    $this->init_page();
    $page = $this->getYml('page/start.yml');
    wfDocument::mergeLayout($page->get());
  }
  public function page_images(){
    /**
     * Init page.
     */
    $this->init_page();
    /**
     * Get data from yml file and sort.
     */
    $image_lib = new PluginWfYml(wfArray::get($GLOBALS, 'sys/web_dir').wfSettings::replaceDir($this->settings->get('web_dir')).'/_image_lib.yml');
    $image_lib = new PluginWfArray(wfArray::sortMultiple($image_lib->get(), 'sort'));
    /**
     * Get upload data file.
     */
    $file_upload_data = new PluginWfYml('/plugin/image/lib_v1/form/plugin_upload_file.yml', 'file_upload_data');
    /**
     * Set upload data file.
     */
    $file_upload_data->set('dir', wfArray::get($GLOBALS, 'sys/web_dir').$this->settings->get('web_dir'));
    $file_upload_data->set('web_dir', $this->settings->get('web_dir'));
    /**
     * Get elements.
     */
    $page = $this->getYml('page/images.yml');
    $image_row = $this->getYml('element/image_row.yml');
    /**
     * Create elements.
     */
    $element = array();
    foreach ($image_lib->get() as $key => $value) {
      /**
       * Set upload data file for each element.
       */
      $file_upload_data->set('id', "form_$key");
      $file_upload_data->set('url', 'upload?key='.$key);
      $file_upload_data->set('name', "$key.jpg");
      /**
       * 
       */
      $widget_upload_file = wfDocument::createWidget('upload/file', 'element', $file_upload_data->get());
      $item = new PluginWfArray($value);
      $image_row->setById('name', 'innerHTML', $item->get('name'));
      $image_row->setById('description', 'innerHTML', $item->get('description'));
      $image_row->setById('link', 'innerHTML', $item->get('link'));
      $image_row->setById('link_target_blank', 'innerHTML', $item->get('link_target_blank'));
      $image_row->setById('sort', 'innerHTML', $item->get('sort'));
      $image_row->setById('img', 'innerHTML', array($widget_upload_file));
      $image_row->setById('btn_edit', 'attribute/data-id', $key);
      $element[] = $image_row->get('image_row');
    }
    /**
     * Set elements.
     */
    $page->setById('image_rows', 'innerHTML', $element);
    /**
     * Render.
     */
    wfDocument::mergeLayout($page->get());
  }
  public function page_widget_list(){
    wfPlugin::enable('image/lib_v1');
    $this->init_page();
    $page = $this->getYml('page/widget_list.yml');
    $page->setById('widget_list', 'data/data/web_dir', $this->settings->get('web_dir'));
    $widget_settings = new PluginWfArray();
    $widget_settings->set('type', 'widget');
    $widget_settings->set('data/plugin', 'image/lib_v1');
    $widget_settings->set('data/method', 'list');
    $widget_settings->set('data/data/web_dir', $this->settings->get('web_dir'));
    $page->setById('widget_settings', 'innerHTML', wfHelp::getYmlDump($widget_settings->get()));
    wfDocument::mergeLayout($page->get());
  }
  public function page_widget_carousel(){
    wfPlugin::enable('image/lib_v1');
    $this->init_page();
    $page = $this->getYml('page/widget_carousel.yml');
    $page->setById('widget_carousel', 'data/data/web_dir', $this->settings->get('web_dir'));
    $page->setById('widget_carousel', 'data/data/height', 300);
    $widget_settings = new PluginWfArray();
    $widget_settings->set('type', 'widget');
    $widget_settings->set('data/plugin', 'image/lib_v1');
    $widget_settings->set('data/method', 'carousel');
    $widget_settings->set('data/data/web_dir', $this->settings->get('web_dir'));
    $widget_settings->set('data/data/height', 300);
    $page->setById('widget_settings', 'innerHTML', wfHelp::getYmlDump($widget_settings->get()));
    wfDocument::mergeLayout($page->get());
  }
  public function widget_carousel($data){
    $data = new PluginWfArray($data);
    if(!$data->get('data/height')){
      $data->set('data/height', 300);
    }
    /**
     * Get data from yml file.
     */
    $image_lib = new PluginWfYml(wfArray::get($GLOBALS, 'sys/web_dir').wfSettings::replaceDir($data->get('data/web_dir')).'/_image_lib.yml');
    $image_lib = new PluginWfArray(wfArray::sortMultiple($image_lib->get(), 'sort'));
    /**
     * 
     */
    $element = $this->getYml('element/widget_carousel.yml');
    $row = $this->getYml('element/widget_carousel_row.yml');
    $carousel_div_indicators = $element->getById('carousel_div_indicators', 'innerHTML/0');
    //wfHelp::yml_dump($carousel_div_indicators);
    /**
     * Set style.
     */
    $element->set('carousel_style/innerHTML', str_replace('[height]', $data->get('data/height'), $element->get('carousel_style/innerHTML')));
    /**
     * Create elements.
     */
    $rows = array();
    $active = ' active';
    foreach ($image_lib->get() as $key => $value) {
      $item = new PluginWfArray($value);
      $src = $data->get('data/web_dir').'/'.$key.'.jpg?x='.wfCrypt::getUid();
      $row->setById('item', 'attribute/class', "item$active");
      $row->setById('item', 'attribute/style', "background-image: url($src);");
      if($item->get('link')){
        $row->setById('link', 'attribute/href', $item->get('link'));
        if($item->get('link_target_blank')){
          $row->setById('link', 'attribute/target', '_blank');
        }
      }
      $row->setById('name', 'innerHTML', $item->get('name'));
      $row->setById('description', 'innerHTML', $item->get('description'));
      $rows[] = $row->get('row_1');
      $active = null;
    }
    /**
     * Indicators.
     */
    $indicators = array();
    $active = ' active';
    $i = 0;
    foreach ($image_lib->get() as $key => $value) {
      $item = new PluginWfArray($value);
      $carousel_div_indicators->set('attribute/class', $active);
      $carousel_div_indicators->set('attribute/data-slide-to', $i);
      $carousel_div_indicators->set('attribute/title', $item->get('name'));
      $indicators[] = $carousel_div_indicators->get();
      $active = null;
      $i++;
    }
    //wfHelp::yml_dump($indicators, true);
    $element->setById('carousel_div_indicators', 'innerHTML', $indicators);
    /**
     * Set elements.
     */
    $element->setById('carousel_rows', 'innerHTML', $rows);
    //wfHelp::yml_dump($element, true);
    /**
     * 
     */
    wfDocument::renderElement($element->get());
  }
  public function widget_list($data){
    $data = new PluginWfArray($data);
    /**
     * Get data from yml file.
     */
    $image_lib = new PluginWfYml(wfArray::get($GLOBALS, 'sys/web_dir').wfSettings::replaceDir($data->get('data/web_dir')).'/_image_lib.yml');
    $image_lib = new PluginWfArray(wfArray::sortMultiple($image_lib->get(), 'sort'));
    /**
     * 
     */
    $element = $this->getYml('element/widget_list.yml');
    $row = $this->getYml('element/widget_list_row.yml');
    /**
     * Create elements.
     */
    $rows = array();
    foreach ($image_lib->get() as $key => $value) {
      $item = new PluginWfArray($value);
      $row->setById('name', 'innerHTML', $item->get('name'));
      $row->setById('description', 'innerHTML', $item->get('description'));
      $row->setById('btn_image', 'attribute/href', $data->get('data/web_dir').'/'.$key.'.jpg?x='.wfCrypt::getUid());
      $row->setById('img', 'attribute/src', $data->get('data/web_dir').'/'.$key.'.jpg?x='.wfCrypt::getUid());
      $row->setById('btn_link', 'innerHTML', $item->get('link'));
      $row->setById('btn_link', 'attribute/href', $item->get('link'));
      if($item->get('link_target_blank')){
        $row->setById('btn_link', 'attribute/target', '_blank');
      }
      $rows[] = $row->get('image_row');
    }
    /**
     * Set elements.
     */
    $element->setById('image_rows', 'innerHTML', $rows);
    /**
     * 
     */
    wfDocument::renderElement($element->get());
  }
  /**
   * Upload page.
   */
  public function page_upload(){
    $this->init_page();
    $file_upload_data = new PluginWfYml('/plugin/image/lib_v1/form/plugin_upload_file.yml', 'file_upload_data');
    $file_upload_data->set('dir', wfArray::get($GLOBALS, 'sys/web_dir').$this->settings->get('web_dir'));
    $file_upload_data->set('web_dir', $this->settings->get('web_dir'));
    $file_upload_data->set('name', wfRequest::get('key').'.jpg');
    $widget_upload_file = wfDocument::createWidget('upload/file', 'capture', $file_upload_data->get());
    wfDocument::renderElement(array($widget_upload_file));
  }
  public function page_edit(){
    $this->init_page();
    $widget = wfDocument::createWidget('wf/form_v2', 'render', 'yml:/plugin/image/lib_v1/form/edit_form.yml');
    wfDocument::renderElement(array($widget));
    $id = wfRequest::get('id');
    if($id){
      $btn_delete = wfDocument::createHtmlElement('a', 'Delete', array('onclick' => "PluginWfAjax.callbackjson('delete/id/$id');"));
      wfDocument::renderElement(array($btn_delete));
    }
  }
  public function page_save(){
    $this->init_page();
    $widget = wfDocument::createWidget('wf/form_v2', 'capture', 'yml:/plugin/image/lib_v1/form/edit_form.yml');
    wfDocument::renderElement(array($widget));
  }
  public function page_delete(){
    $this->init_page();
    $image_lib = new PluginWfYml(wfArray::get($GLOBALS, 'sys/web_dir').wfSettings::replaceDir($this->settings->get('web_dir')).'/_image_lib.yml');
    $id = wfRequest::get('id');
    if(wfFilesystem::fileExist(wfArray::get($GLOBALS, 'sys/web_dir').wfSettings::replaceDir($this->settings->get('web_dir')).'/'.$id.'.jpg')){
      wfFilesystem::delete(wfArray::get($GLOBALS, 'sys/web_dir').wfSettings::replaceDir($this->settings->get('web_dir')).'/'.$id.'.jpg');
    }
    $image_lib->setUnset($id);
    $image_lib->save();
    exit(json_encode(array('success' => true, 'script' => array("PluginWfAjax.update('start_content');$('.modal').modal('hide');"))));
  }
  /**
   * 
   */
  private function getYml($file, $path_to_key = null){
    return new PluginWfYml(wfArray::get($GLOBALS, 'sys/app_dir').'/plugin/image/lib_v1/'.$file, $path_to_key);
  }
  public function edit_form_render($form){
    $this->init_page();
    $image_lib = new PluginWfYml(wfArray::get($GLOBALS, 'sys/web_dir').wfSettings::replaceDir($this->settings->get('web_dir')).'/_image_lib.yml');
    $id = wfRequest::get('id');
    if(!$id){
      $id = wfCrypt::getUid();
    }
    $image_lib->set("$id/id", $id);
    $form = new PluginWfArray(PluginWfForm_v2::setDefaultsFromArray($form->get(), $image_lib->get($id)));
    return $form;
  }
  public function edit_form_capture($form){
    $this->init_page();
    $id = wfRequest::get('id');
    $image_lib = new PluginWfYml(wfArray::get($GLOBALS, 'sys/web_dir').wfSettings::replaceDir($this->settings->get('web_dir')).'/_image_lib.yml');
    $image_lib->set("$id/name", $form->get('items/name/post_value'));
    $image_lib->set("$id/description", $form->get('items/description/post_value'));
    $image_lib->set("$id/link", $form->get('items/link/post_value'));
    $image_lib->set("$id/link_target_blank", $form->get('items/link_target_blank/post_value'));
    $image_lib->set("$id/sort", $form->get('items/sort/post_value'));
    $image_lib->save();
    return array("PluginWfAjax.update('start_content');$('.modal').modal('hide');");
  }
}
