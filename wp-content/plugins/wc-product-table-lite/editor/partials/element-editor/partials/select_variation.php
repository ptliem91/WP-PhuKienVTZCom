<a href="https://wcproducttable.com/documentation/select-variation/" target="_blank" class="wcpt-how-to-use">
  <?php wcpt_icon('file-text'); ?>
  <span>How to use</span>
</a>

<div class="wcpt-editor-row-option">
  <label>
    Display type
  </label>
  <select class="" wcpt-model-key="display_type">
    <option value="dropdown">Dropdown with all variations</option>
    <option value="radio_multiple">Radio buttons for all variations</option>
    <option value="radio_single">Single radio button &mdash; for 1 variation</option>
  </select>
</div>

<!-- single radio options -->
<div class="wcpt-editor-row-option"
  wcpt-panel-condition="prop"
  wcpt-condition-prop="display_type"
  wcpt-condition-val="dropdown||radio_multiple"
>

  <div class="wcpt-editor-row-option">
    <label>
      <input type="checkbox" wcpt-model-key="hide_attributes">
      Hide attributes from variation name
      <small>
        Eg: "Size: Large, Gluten: Gluten free" becomes "Large, Gluten free"
      </small>
    </label>
  </div>

  <div class="wcpt-editor-row-option">
    <label>
      Separator between each attribute and term
      <small>
        A character to show separating between the attribute and the term. Eg: : - &mdash;
      </small>
    </label>
    <input type="text" wcpt-model-key="attribute_term_separator">
  </div>

  <div class="wcpt-editor-row-option">
    <label>
      Separator between attributes
      <small>
        A character to show separating between the attributes. Eg: , | & ::
      </small>
    </label>
    <input type="text" wcpt-model-key="attribute_separator">
  </div>

</div>

<!-- single radio options -->
<div class="wcpt-editor-row-option"
  wcpt-panel-condition="prop"
  wcpt-condition-prop="display_type"
  wcpt-condition-val="radio_single"
>

  <div class="wcpt-editor-row-option">
    <label>
      Variation name
      <small>Give this variation a name</small>
    </label>
    <input type="text" wcpt-model-key="variation_name" />
  </div>

  <div class="wcpt-editor-row-option" wcpt-model-key="attribute_terms">

    <label>
      Specify all attribute-terms of this variation
      <small>This list will help WCPT identify the variation</small>
    </label>

    <div
      class="wcpt-editor-row wcpt-editor-select-variation-attribute-term"
      wcpt-controller="taxonomy_terms"
      wcpt-model-key="[]"
      wcpt-model-key-index="0"
      wcpt-row-template="identify_variation"
    >
      <select wcpt-model-key="taxonomy">
        <option value="">Attribute</option>
        <?php
          foreach( $attributes as $attribute ){
            echo '<option value="pa_'. $attribute->attribute_name .'">' . $attribute->attribute_label . '</option>';
          }
        ?>
      </select>
      <select wcpt-model-key="term">
        <option value="">Term</option>
      </select>
      <span class="wcpt-loading-term" style="display: none;"><?php wcpt_icon('loader', 'wcpt-rotate'); ?> Loading...</span>
      <span class="wcpt-remove-item" wcpt-remove-row title="Delete row"><?php wcpt_icon('x') ?></span>
    </div>

    <button
      class="wcpt-button"
      wcpt-add-row-template="identify_variation"
    >
      Add another
    </button>

  </div>

  <div class="wcpt-editor-row-option">
    <label>
      Template
      <small>Placeholder: [variation_name]</small>
    </label>
    <div
      wcpt-block-editor=""
      wcpt-be-add-element-partial="add-variation-element"
      wcpt-be-add-row="1"
      wcpt-model-key="template"
    ></div>
  </div>

  <div class="wcpt-editor-row-option">
    <label>
      Output if this variation does not exist for the product
      <small>Leave empty for no output</small>
    </label>
    <div
      wcpt-block-editor=""
      wcpt-be-add-element-partial="add-common-element"
      wcpt-be-add-row="1"
      wcpt-model-key="not_exist_template"
    ></div>
  </div>

  <!-- style -->
  <div class="wcpt-editor-row-option" wcpt-model-key="style">

    <div class="wcpt-editor-row-option wcpt-toggle-options wcpt-row-accordion" wcpt-model-key="[id]">

      <span class="wcpt-toggle-label">
        Style for Container
        <?php echo wcpt_icon('chevron-down'); ?>
      </span>

      <?php require( 'style/common-props.php' ); ?>

    </div>

    <!-- style: out of stock -->
    <div class="wcpt-editor-row-option wcpt-toggle-options wcpt-row-accordion" wcpt-model-key="[id].wcpt-variation-out-of-stock">

      <span class="wcpt-toggle-label">
        Style when variation is 'Out of Stock'
        <?php echo wcpt_icon('chevron-down'); ?>
      </span>

      <?php require( 'style/common-props.php' ); ?>

    </div>

    <!-- style: checked -->
    <div class="wcpt-editor-row-option wcpt-toggle-options wcpt-row-accordion" wcpt-model-key="[id].wcpt-selected">

      <span class="wcpt-toggle-label">
        Style when variation is 'Selected'
        <?php echo wcpt_icon('chevron-down'); ?>
      </span>

      <?php require( 'style/common-props.php' ); ?>

    </div>

  </div>

</div> <!-- /single radio options -->
