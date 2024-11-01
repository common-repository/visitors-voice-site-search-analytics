jQuery(document).ready(function() {
	jQuery('#searchterms_refinements_table').dataTable({
		"iDisplayLength": 5,
		"aLengthMenu": [5, 10, 25, 50],
		"aaSorting": [[ 2, "desc" ]],
		"aoColumns": [
			{ "sWidth": "34%", },
			{ "sWidth": "22%", },
            { "sWidth": "22%", },
            { "sWidth": "22%", },                
        ],
		"oLanguage": {
			"sEmptyTable": "Tabellen innehåller ingen data",
			"sInfo": "Visar _START_ till _END_ av totalt _TOTAL_ rader",
			"sInfoEmpty": "Visar 0 till 0 av totalt 0 rader",
			"sInfoFiltered": "(filtrerade från totalt _MAX_ rader)",
			"sInfoPostFix": "",
			"sInfoThousands": ",",
			"sLengthMenu": "Visa _MENU_ rader",
			"sLoadingRecords": "Laddar...",
			"sProcessing": "Bearbetar...",
			"sSearch": "Sök:",
			"sZeroRecords": "Hittade inga matchande resultat",
			"oPaginate": {
				"sFirst": "Första",
				"sLast": "Sista",
				"sNext": "Nästa",
				"sPrevious": "Föregående"
			},
			"oAria": {
				"sSortAscending": ": aktivera för att sortera kolumnen i stigande ordning",
				"sSortDescending": ": aktivera för att sortera kolumnen i fallande ordning"
			}
		}
	});
	
	jQuery('#incoming_searchterms_table').dataTable({
		"iDisplayLength": 5,
		"aLengthMenu": [5, 10, 25, 50],
		"aaSorting": [[ 1, "desc" ]],
		"aoColumns": [
			{ "sWidth": "34%", },
			{ "sWidth": "22%", },
            { "sWidth": "22%", },
            { "sWidth": "22%", }, 			
        ],
		"oLanguage": {
			"sEmptyTable": "Tabellen innehåller ingen data",
			"sInfo": "Visar _START_ till _END_ av totalt _TOTAL_ rader",
			"sInfoEmpty": "Visar 0 till 0 av totalt 0 rader",
			"sInfoFiltered": "(filtrerade från totalt _MAX_ rader)",
			"sInfoPostFix": "",
			"sInfoThousands": ",",
			"sLengthMenu": "Visa _MENU_ rader",
			"sLoadingRecords": "Laddar...",
			"sProcessing": "Bearbetar...",
			"sSearch": "Sök:",
			"sZeroRecords": "Hittade inga matchande resultat",
			"oPaginate": {
				"sFirst": "Första",
				"sLast": "Sista",
				"sNext": "Nästa",
				"sPrevious": "Föregående"
			},
			"oAria": {
				"sSortAscending": ": aktivera för att sortera kolumnen i stigande ordning",
				"sSortDescending": ": aktivera för att sortera kolumnen i fallande ordning"
			}
		}
	});
	
	jQuery('#outgoing_searchterms_table').dataTable({
		"iDisplayLength": 5,
		"aLengthMenu": [5, 10, 25, 50],
		"aaSorting": [[ 1, "desc" ]],
		"aoColumns": [
			{ "sWidth": "34%", },
			{ "sWidth": "22%", },
            { "sWidth": "22%", },
            { "sWidth": "22%", },  			
        ],
		"oLanguage": {
			"sEmptyTable": "Tabellen innehåller ingen data",
			"sInfo": "Visar _START_ till _END_ av totalt _TOTAL_ rader",
			"sInfoEmpty": "Visar 0 till 0 av totalt 0 rader",
			"sInfoFiltered": "(filtrerade från totalt _MAX_ rader)",
			"sInfoPostFix": "",
			"sInfoThousands": ",",
			"sLengthMenu": "Visa _MENU_ rader",
			"sLoadingRecords": "Laddar...",
			"sProcessing": "Bearbetar...",
			"sSearch": "Sök:",
			"sZeroRecords": "Hittade inga matchande resultat",
			"oPaginate": {
				"sFirst": "Första",
				"sLast": "Sista",
				"sNext": "Nästa",
				"sPrevious": "Föregående"
			},
			"oAria": {
				"sSortAscending": ": aktivera för att sortera kolumnen i stigande ordning",
				"sSortDescending": ": aktivera för att sortera kolumnen i fallande ordning"
			}
		}
	});
	
	jQuery('#incoming_keywords_table').dataTable({
		"iDisplayLength": 5,
		"aLengthMenu": [5, 10, 25, 50],
		"aaSorting": [[ 1, "desc" ]],
		"aoColumns": [
			{ "sWidth": "34%", },
			{ "sWidth": "34%", },
            { "sWidth": "32%", },                
        ],
		"oLanguage": {
			"sEmptyTable": "Tabellen innehåller ingen data",
			"sInfo": "Visar _START_ till _END_ av totalt _TOTAL_ rader",
			"sInfoEmpty": "Visar 0 till 0 av totalt 0 rader",
			"sInfoFiltered": "(filtrerade från totalt _MAX_ rader)",
			"sInfoPostFix": "",
			"sInfoThousands": ",",
			"sLengthMenu": "Visa _MENU_ rader",
			"sLoadingRecords": "Laddar...",
			"sProcessing": "Bearbetar...",
			"sSearch": "Sök:",
			"sZeroRecords": "Hittade inga matchande resultat",
			"oPaginate": {
				"sFirst": "Första",
				"sLast": "Sista",
				"sNext": "Nästa",
				"sPrevious": "Föregående"
			},
			"oAria": {
				"sSortAscending": ": aktivera för att sortera kolumnen i stigande ordning",
				"sSortDescending": ": aktivera för att sortera kolumnen i fallande ordning"
			}
		}
	});
});