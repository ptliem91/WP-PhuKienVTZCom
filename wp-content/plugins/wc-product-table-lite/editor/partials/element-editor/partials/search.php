<!-- placeholder -->
<div class="wcpt-editor-row-option">
  <label>Placeholder</label>
  <input type="text" wcpt-model-key="placeholder">
</div>

<!-- target -->
<div class="wcpt-editor-row-option">
  <label>
    Search through:
  </label>
  <?php
    foreach( array( 'Title', 'Content', 'Title + Content' ) as $target ){
      ?>
      <label>
        <input type="radio" value="<?php echo strtolower( str_replace(array(' + ', ' '), array( '+', '_' ), $target) ); ?>" wcpt-model-key="target">
        <?php echo $target; ?>
      </label>
      <?php
    }
    echo '<label>' . wcpt_pro_radio( 'custom_fields', 'Custom fields', 'target' ) . '</label>';
  ?>
</div>

<!-- custom fields -->
<div
  class="wcpt-editor-row-option"
  wcpt-panel-condition="prop"
  wcpt-condition-prop="target"
  wcpt-condition-val="custom_fields"
>
  <label>
    Custom fields
    <small>Enter one custom field key per line</small>
  </label>
  <textarea wcpt-model-key="custom_fields"></textarea>
</div>

<!-- clear label -->
<div class="wcpt-editor-row-option">
  <label>
    Text in 'clear search' option
    <small>use [kw] as placeholder for the search keyword</small>
  </label>
  <input type="text" wcpt-model-key="clear_label" placeholder="Search: [kw]">
</div>

<div class="wcpt-editor-row-option" wcpt-model-key="style">

  <div class="wcpt-editor-row-option wcpt-toggle-options wcpt-row-accordion" wcpt-model-key="[id]">

    <span class="wcpt-toggle-label">
      Style for Element
      <?php echo wcpt_icon('chevron-down'); ?>
    </span>

    <!-- font-size -->
    <div class="wcpt-editor-row-option">
      <label>Font size</label>
      <input type="text" wcpt-model-key="font-size" placeholder="16px" wcpt-initial-data="">
    </div>

    <!-- font-color -->
    <div class="wcpt-editor-row-option">
      <label>Font color</label>
      <input type="text" wcpt-model-key="color" placeholder="#000" class="wcpt-color-picker" >
    </div>

    <!-- width -->
    <div class="wcpt-editor-row-option">
      <label>Force width</label>
      <input type="text" wcpt-model-key="width" />
    </div>

  </div>

</div>


<div class="wcpt-editor-row-option" wcpt-model-key="style">

  <div class="wcpt-editor-row-option wcpt-toggle-options wcpt-row-accordion" wcpt-model-key="[id] .wcpt-search-submit">

    <span class="wcpt-toggle-label">
      Style for Submit Button
      <?php echo wcpt_icon('chevron-down'); ?>
    </span>

    <!-- background-color -->
    <div class="wcpt-editor-row-option">
      <label>Background color</label>
      <input type="text" wcpt-model-key="background-color" class="wcpt-color-picker" >
    </div>

    <!-- color -->
    <div class="wcpt-editor-row-option">
      <label>Icon color</label>
      <input type="text" wcpt-model-key="color" class="wcpt-color-picker" >
    </div>

  </div>

</div>


<div class="wcpt-editor-row-option">
  <label>HTML Class</label>
  <input type="text" wcpt-model-key="html_class" />
</div>
