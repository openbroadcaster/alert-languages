var result = [];
var prev = "";
$('tbody:eq(2) tr').each(function (i, e) {
	var row = {};
	$(e).find('td:eq(0), td:eq(2), td:eq(4)').each(function(j, f) { 
        var val = $(f).text().trim();
        switch (j) {
          case 0:
            var key = "tier1";
            if (val == "\"" || val == "“" || val == "") { 
              val = prev;
            } else {
              prev = val;
            }
            break;
          case 1: 
            var key = "tier2";
            break;
          case 2:
            var key = "event";
            break;
          default: 
            console.log("this shouldn't happen.");
            break;
        }
		row[key] = val;
	}); 
	result.push(row);
});
result.splice(0, 2);
JSON.stringify(result);