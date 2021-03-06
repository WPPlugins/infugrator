(function($){

	PubSub.subscribe( 'add_data_charts', function(msg, res){

		if(res.hasOwnProperty('contactform7')){

		    google.charts.load('current', {packages: ['corechart', 'bar']});
	        google.charts.setOnLoadCallback(function() {

	            var target = jQuery('#'+res.contactform7.target);

	            target.removeClass('loading').find('.fa-spinner').remove().promise().done(function(){

	                var data = google.visualization.arrayToDataTable(res.contactform7.data);

                    var view = new google.visualization.DataView(data);
                        view.setColumns([0, 1, {
                            calc: "stringify",
                            sourceColumn: 1,
                            type: "string",
                            role: "annotation"
                        }, 2]);

                    var options = {
                        vAxis: {format: ''},
                        chartArea: {width: '80%'},
                        height: 300,
                        bar: {groupWidth: '80%'},
                        legend: { position: 'none' },
                    };

                    var chart = new google.visualization.ColumnChart(document.getElementById(res.contactform7.target));
	                chart.draw(view, options);

	            });
	        });
	    }
	});

})(jQuery)