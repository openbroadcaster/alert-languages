OBModules.AlertLanguages = new function()
{

  /* Module initialization. */
  this.init = function () {
    OB.Callbacks.add('ready',0,OBModules.AlertLanguages.initMenu);
  }

  this.initMenu = function () {
    OB.UI.addSubMenuItem('admin','Alert Languages','alert_languages',OBModules.AlertLanguages.open,150,'alert_languages_module');
  }
  
  /* Module language overview functionality. */
  this.open = function () {
    OB.UI.replaceMain('modules/alert_languages/alert_languages.html');
    
    OBModules.AlertLanguages.getLanguages();
  }
  
  this.getLanguages = function () {
    $('#alert_languages_langs').empty();
    
    OB.API.post('alertlanguages', 'get_languages', {}, function (response) {
      if (!response.status) {
        $('#alert_languages_message').obWidget('error', response.msg);
      }
      
      var table   = $('<table/>');
      var table_h = $('<tr/>');
      table_h.append($('<th/>').text('Code'));
      table_h.append($('<th/>').text('Language Name'));
      table_h.append($('<th/>').text('Alerts'));
      table_h.append($('<th/>').text(''));
      table_h.append($('<th/>').text(''));
      table.append(table_h);
      
      $(response.data).each(function (index, element) {
        var item = $('<tr/>').attr('data-id', element.id);
        item.append($('<td/>').text(element.code));
        item.append($('<td/>').text(element.name));
        item.append($('<td/>').text(element.media_items));
        
        item.append($('<td/>').append($('<a/>')
          .text('View')
          .click(function () {
            var lang_id = $(this).parents('tr').first().attr('data-id');
            OBModules.AlertLanguages.viewLanguage(lang_id);
          })
          .addClass('button')));
        
        item.append($('<td/>').append($('<a/>')
          .text('Delete')
          .click(function () {
            var lang_id = $(this).parents('tr').first().attr('data-id');
            OBModules.AlertLanguages.deleteLanguage(lang_id);
          })
          .addClass('button')
          .addClass('delete')));
        
        table.append(item);
      });
      
      $('#alert_languages_langs').append(table);
    });
  }
  
  this.addLanguage = function () {
    OB.UI.openModalWindow('modules/alert_languages/alert_languages_newlang.html');
  }
  
  this.saveLanguage = function () {
    post = {};
    post.name = $('#alert_languages_lang_name').val();
    post.code = $('#alert_languages_lang_code').val();
    
    OB.API.post('alertlanguages', 'save_language', post, function (response) {
      if (response.status) {
        OB.UI.closeModalWindow();
        OBModules.AlertLanguages.open();
        $('#alert_languages_message').obWidget('success', response.msg);
      } else {
        $('#alert_languages_lang_message').obWidget('error', response.msg);
      }
    });
  }
  
  this.deleteLanguage = function (lang_id) {
    OB.UI.confirm({
      text: "Are you sure you want to delete this alert language?",
      okay_class: "delete",
      callback: function () {
        OBModules.AlertLanguages.deleteLanguageConfirm(lang_id);
      }
    });
  }
  
  this.deleteLanguageConfirm = function (lang_id) {
    var post = {};
    post.lang_id = lang_id;
    
    OB.API.post('alertlanguages', 'delete_language', post, function (response) {
      var msg_result = (response.status ? 'success' : 'error');
      $('#alert_languages_message').obWidget(msg_result, response.msg);
      
      OBModules.AlertLanguages.getLanguages();
    });
  }
  
  /* Module single language alerts view functionality. */
  this.viewLanguage = function (lang_id) {
    OB.UI.replaceMain('modules/alert_languages/alert_languages_view.html');
    $('#alert_languages_current_id').val(lang_id);
    
    $('#alert_languages_alerts').empty();
    $.getJSON('modules/alert_languages/html/alerts.json', function (data) {
      var table = $('<table/>').addClass('table');
      
      var table_h = $('<tr/>');
      table_h.append($('<th/>').text('Tier I'));
      table_h.append($('<th/>').text('Tier II'));
      table_h.append($('<th/>').text('Event Code'));
      table.append(table_h);
      
      $(data).each(function (index, element) {
        var item = $('<tr/>').attr('data-code', element.event);
        item.append($('<td/>').text(element.tier1));
        item.append($('<td/>').text(element.tier2));
        item.append($('<td/>').text(element.event));
        item.append($('<td/>')
            .attr('id', 'drop_' + element.event)
            .addClass('droppable_target_media')
            .text('<no media item>'));
        table.append(item);
      });
      
      $('#alert_languages_alerts').append(table);
      
      $(data).each(function (index, element) {
        var drop_id = '#drop_' + element.event;
        $(drop_id).droppable({
          drop: function (event, ui) {
            OBModules.AlertLanguages.droppableAlert(event, ui, drop_id);
          }
        });  
      });
      
      var post = {};
      post.lang_id = lang_id;
      OB.API.post('alertlanguages', 'view_language', post, function (response) {
        var msg_result = (response.status ? 'success' : 'error');
        if (msg_result == 'error') {
          OBModules.AlertLanguages.open();
          $('#alert_languages_message').obWidget(msg_result, response.msg);
        }

        $(response.data).each(function (i, e) {
          var elem = $('#alert_languages_alerts tr[data-code=' + e.alert_name + '] td:nth-child(4)');
          elem.empty();
          var del_link = $('<a/>')
              .text('x')
              .click(function () {
                OBModules.AlertLanguages.deleteAlert($(this));
              });
          var label = ' ' + e.artist + ' - ' + e.title;
          elem.attr('data-id', e.media_id);
          elem.append(del_link);
          elem.append(label);
        });
      });
    });
  }
  
  this.droppableAlert = function (event, ui, drop_id) {
    if ($(ui.draggable).attr('data-mode') == 'media') {
      var sel = $('.sidebar_search_media_selected:eq(0)');
      if (sel.length != 0) {
        if (sel.attr('data-public_status') == 'private') {
          OB.UI.alert("Cannot use private items.");
          return true;
        }
        
        var data_id = sel.attr('data-id');
        var label = sel.attr('data-artist') + ' - ' + sel.attr('data-title');
        $(drop_id).attr('data-id', data_id);
        $(drop_id).text(label);
        OB.Sidebar.mediaSelectNone();
      }
    }
  }
  
  this.updateAlerts = function () {
    var post = {};
    post.language = $('#alert_languages_current_id').val();
    post.alerts = [];
    $('#alert_languages_alerts tr td:nth-child(4)').each(function (i, e) { 
      if ($(e).data('id') != undefined) {
        var event = $(e).parent().data('code');
        var media = $(e).data('id');
        post.alerts.push({
          event: event,
          media: media
        });
      }
    });
    
    OB.API.post('alertlanguages', 'update_alerts', post, function (response) {
      var msg_result = (response.status ? 'success' : 'error');
      $('#alert_languages_message').obWidget(msg_result, response.msg);
    });
    
    OBModules.AlertLanguages.viewLanguage($('#alert_languages_current_id').val());
  }
  
  this.deleteAlert = function (item) {
    $(item).parent().removeAttr('data-id');
    $(item).parent().text('<no media item>');
  }
}
  