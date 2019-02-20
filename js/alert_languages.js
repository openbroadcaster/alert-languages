OBModules.AlertLanguages = new function()
{

  this.init = function()
  {
    OB.Callbacks.add('ready',0,OBModules.AlertLanguages.initMenu);
  }

  this.initMenu = function()
  {
    OB.UI.addSubMenuItem('admin','Alert Languages','alert_languages',OBModules.AlertLanguages.open,150,'alert_languages_module');
  }
  
  this.open = function()
  {
    OB.UI.replaceMain('modules/alert_languages/alert_languages.html');
    
    OB.API.post('alertlanguages','get_alerts',{},function(response)
    {
      $.each(response.data,function(index,alert)
      {
        OBModules.AlertLanguages.addAlertField(alert.alert_name,alert.media_id);
      });
    });
  }
  
  this.addAlertField = function(name,id)
  {
    $div = $('<div class="droppable_target_media" />');
    $div.append( $('<input type="text" placeholder="Alert Name" />').val(name) );
    $div.append( $('<input type="number" placeholder="Media ID" />').val(id) );
    $('#alert_languages_fields').append($div);
    
    $('#alert_languages_fields .droppable_target_media').last().droppable({
      drop: function(event, ui) 
      {
        if($(ui.draggable).attr('data-mode')=='media') 
        {
          $(this).find('input[type=number]').val($(ui.draggable).attr('data-id'));
        }
      }
    });
  }

  this.saveAlerts = function()
  {
    alerts = [];
  
    $('#alert_languages_fields > div').each(function(index, fields)
    {
      var name = $(fields).find('input[type=text]').val();
      var id = $(fields).find('input[type=number]').val();
      if(name && id) alerts.push( {'name': name, 'id': id} );
    });
    
    OB.API.post('alertlanguages','save_alerts',{'alerts': alerts},function(response)
    {
      OBModules.AlertLanguages.open();
      OB.UI.alert('Demo Language alerts saved.');
    });
  }
}
  