(function($, view){

  view.parent;

  view.render = function(){

    var $be = this.parent.$elm,
        parent = this.parent,
        view = this,
        data = this.parent.model.get_data();

    $be.empty(); // make blank

    $.each( data, function( index, row ){
      // create row
      var $row = $('<div class="wcpt-block-editor-row" data-id="'+ row.id +'">').data('wcpt-data', row);

      // append elements to row
      $.each( row.elements, function( el_index, element ){
        var $element = $('<div class="wcpt-element-block" data-type="'+ element.type +'" data-id="'+ element.id +'">'+ view.get_label(element) +'</div>');
        $row.append($element.data('wcpt-data', element));
      } )

      // add element trigger
      var $add_element = $('<a href="#" class="wcpt-block-editor-add-element">+ Add Element</a>');
      $row.append($add_element);

      // edit row trigger
      if( parent.config.edit_row_partial ){
        var icon = $('#wcpt-icon-sliders').length ? $('#wcpt-icon-sliders').text() : '*',
            $settings = $('<span class="wcpt-block-editor-edit-row" title="Edit row settings">'+ icon +'</span>');
        $row.append($settings);
      }

      // delete row trigger
      if( parent.config.delete_row && data.length > 1 ){
        var icon = $('#wcpt-icon-x').length ? $('#wcpt-icon-x').text() : 'x',
            $del = $('<span class="wcpt-block-editor-delete-row" title="Delete row">'+ icon +'</span>');
        $row.append($del);
      }

      // append row to editor
      $be.append($row);
    } );

    // add row trigger
    if( this.parent.config.add_row ){
      var $add_row = $('<a href="#" class="wcpt-block-editor-add-row">+ Add Row</a>');
      $be.append( $add_row );
    }

    // make rows sortable
    if( $('.wcpt-block-editor-row', $be).length > 1 ){
      $be.sortable({
        items: '.wcpt-block-editor-row',
        disabled: false,
      });
    }else{
      $be.sortable({
        items: '.wcpt-block-editor-row',
        disabled: true,
      });
    }

    // connect with
    var cw = this.parent.config.connect_with,
        $lb = $be.closest('.wcpt-block-editor-lightbox-screen');
    if( $lb.length ){
      cw = '[data-partial="'+ $lb.attr('data-partial') +'"].wcpt-block-editor-lightbox-screen ' + cw;
    }

    // make blocks sortable
  	$('.wcpt-block-editor-row', $be).sortable({
      items: '.wcpt-element-block',
  		connectWith: cw,
  		placeholder: 'wcpt-element-block-placeholder',
  		forcePlaceholderSize: true,
  		start: function(event, ui){
  			// helper size
  			ui.helper
  				.width( ui.helper.width() + 1 )
  				.height('');

  			// placeholder width
  			ui.placeholder
  				.width( ui.item.outerWidth() )
  				.addClass('wcpt-element-block');

        // $(this).addClass('wcpt-block-editor-sorting');
        // $(this).siblings().addClass('wcpt-block-editor-sorting');
  		},
      sort: function(event, ui) {
          var $target = $(event.target);
          if( $target.closest('.wcpt-block-editor-lightbox-screen').length ){
            if (!/html|body/i.test($target.offsetParent()[0].tagName)) {
              var top = event.pageY - $target.offsetParent().offset().top - (ui.helper.outerHeight(true) * 1.5);
              ui.helper.css({'top' : top + 'px'});
            }
          }

      }

  	});

  }

  view.lightbox = function(options){

    var default_ops = {
      $element: null,
      duplicate_remove: true,
      attr: {}
    };

    $.extend( true, default_ops, options );

    // create
    var $lightbox       = $('<div class="wcpt-block-editor-lightbox-screen"><div class="wcpt-block-editor-lightbox-content"></div></div>'),
        $tray           = $('<div class="wcpt-block-editor-lightbox-tray"></div>'),
        done_icon       = $('#wcpt-icon-check').length ? $('#wcpt-icon-check').text() : '',
        $done           = $('<span class="wcpt-block-editor-lightbox-done" title="Done">'+ done_icon +'</span>'),
        close_icon      = $('#wcpt-icon-x').length ? $('#wcpt-icon-x').text() : '',
        $close          = $('<span class="wcpt-block-editor-lightbox-close" title="Close">'+ close_icon +'</span>'),
        remove_icon     = $('#wcpt-icon-trash').length ? $('#wcpt-icon-trash').text() : '',
        $remove         = $('<span class="wcpt-block-editor-lightbox-remove" title="Trash">'+ remove_icon +'</span>'),
        duplicate_icon  = $('#wcpt-icon-copy').length ? $('#wcpt-icon-copy').text() : '',
        $duplicate      = $('<span class="wcpt-block-editor-lightbox-duplicate" title="Clone">'+ duplicate_icon +'</span>');


    if( options.duplicate_remove ){
      $tray.append( $done );
      $tray.append( $duplicate.add($remove) );
    }else{
      $tray.append( $close );
    }

    $('> .wcpt-block-editor-lightbox-content', $lightbox).append($tray);

    $('body').append($lightbox);

    $lightbox
      .data('wcpt-block-element', options.$element ? options.$element : '')
      .attr({
        'wcpt-controller': 'edit-element-lightbox',
        'wcpt-model-key': 'block_element_data',
      })
      .attr(options.attr)
      .children('.wcpt-block-editor-lightbox-content')
        .append($('script[data-wcpt-partial="'+ options.partial +'"]').text())
        .end()
      .show();

    // add flag modal flag
    $('body').addClass('wcpt-be-lightbox-on');

    // destroy
    // -- via screen click
    var _ = this;
    $lightbox.on('click', function(e){
      if( $(e.target).is($lightbox) ){
        $lightbox.trigger('destroy');
      }
    })
    // -- via close 'X' click
    $( '> .wcpt-block-editor-lightbox-content > .wcpt-block-editor-lightbox-tray > .wcpt-block-editor-lightbox-close, > .wcpt-block-editor-lightbox-content > .wcpt-block-editor-lightbox-tray > .wcpt-block-editor-lightbox-done', $lightbox ).on('click', function(){
      $lightbox.trigger('destroy');
    })
    // -- destroy event handler
    $lightbox.on('destroy', function(){
      $lightbox.change();
      $lightbox.remove();

      // remove flag modal flag
      if( ! $( '.wcpt-block-editor-lightbox-screen' ).length ){
        $('body').removeClass('wcpt-be-lightbox-on');
      }
    })

    return $lightbox;
  }

  view.get_label = function(element){

    var type_unslug = element.type.replace(/(_|^)([^_]?)/g, function(_, prep, letter) {
            return (prep && ' ') + letter.toUpperCase();
        }),
        label = type_unslug;

    switch (element.type) {
      case 'attribute':
      case 'attribute_filter':
        if( element.attribute_name ){
          label = element.attribute_name;
          if( typeof window.wcpt_attributes == 'object' ){
            $.each(window.wcpt_attributes, function(key, val){
              if( val.attribute_name == element.attribute_name ){
                label = val.attribute_label;
              }
            })
          }
          label = 'AT: ' + label.charAt(0).toUpperCase() + label.substr(1);
        }
        break;

      case 'custom_field':
      case 'custom_field_filter':
        if( element.field_name ){
          label = 'CF: ' + element.field_name;
        }
        break;

      case 'text':
        if( element.text ){
          label = String(element.text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
          if(label.length > 20){
            label = label.substring(0, 20) + '...';
          }
          label = 'Text: "' + label + '"';
        }
        break;

      case 'text__col':
        if( element.text ){
          label = String(element.text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
          if(label.length > 20){
            label = label.substring(0, 20) + '...';
          }
          label = 'Text: "' + label + '"';
        }else{
          label = 'Text';
        }
        break;

      case 'html' :
        if( element.html ){
          label = element.html.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');;
          if(label.length > 20){
            label = label.substring(0, 20) + '...';
          }
          label = 'HTML: "' + label + '"';
        }else{
          label = 'HTML';
        }
        break;

      case 'sku' :
        label = 'SKU';
        break;

      case 'sorting':
        if( element.orderby ){
          label = 'Sort by: ';

          if( element.orderby == 'meta_value_num' || element.orderby == 'meta_value' ){
            label += ' CF - ' + element.meta_key;

          }else{
            label += element.orderby[0].toUpperCase() + element.orderby.substring(1);

          }

        }
        break;

      case 'icon':
        if( element.name ){
          label = '<img class="wcpt-icon-rep" src="'+ wcpt_icons + element.name +'.svg">';
        }
        break;

      case 'dot':
        label = '⋅';
        break;

      case 'bang':
        label = '|';
        break;

      case 'select_variation':
        label = 'Select variation';

        // radio single
        if( typeof element.display_type == 'undefined' || element.display_type == 'radio_single' ){

          if( element.variation_name ){
            var name = element.variation_name.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            label = 'Select variation: <span style="opacity: .75; font-style: italic;">' + name + '</span>';

          }else{
            label = 'Select variation: <span style="opacity: .75; font-style: italic;">*Single variation*</span>';
          }

        // radio multiple
        }else if( element.display_type == 'radio_multiple' ){
          label = 'Select variation: <span style="opacity: .75; font-style: italic;">*Radio buttons*</span>';

        // dropdown
        }else if( element.display_type == 'dropdown' ){
          label = 'Select variation: <span style="opacity: .75; font-style: italic;">*Dropdown*</span>';
        }

        break;

    }

    if( element.type.split('__').length == 2 && -1 == $.inArray( element.type, ['text__col'] ) ){
      var string = element.type.split('__')[0];
      label = string.charAt(0).toUpperCase() + string.slice(1);
    }

    return label;
  },

  view.mark_elm = function( row_index, elm_index ){
    var $be = this.parent.$elm,
        $row = $be.children( '.wcpt-block-editor-row' ).eq(row_index),
        $target;

    if( $row.length ){
      var $elm = $row.children( '.wcpt-element-block' ).eq(elm_index);

      if( $elm.length ){
        $target = $elm;
      }else{
        $target = $row;
      }
    }

    $target.addClass('wcpt-be-mark');
    setTimeout( function(){
      $target.removeClass('wcpt-be-mark');
    }, 1250 );
  }

})( jQuery, WCPT_Block_Editor.View );
