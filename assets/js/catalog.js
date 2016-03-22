$(function(){
    var base_uri = '/shops/';

    var myMap = null;
    var initMap = false;

    /* Catlog menu handler */
    $('#catalogMenu a.partlink').click(function(e){
        e.preventDefault();
        $('#catalogMenu li>ul:visible').toggle(150);
        $(this).siblings('ul:hidden').toggle(150);
    });

    /* Отправить сообщение пользователю */
    $('#sendMessage').click(function(e){
        $('body').off('click', '#cancel_mailto');
        $('body').off('submit', '#mailtoForm');
        e.preventDefault();
        var this_id = $(this).data('id')
        $.ajax({
            url: base_uri + "send_message/"+ this_id,
            dataType: "json",
            success: function(data){
                $('#mailto').html(data.content);
                $('body').on('click', '#cancel_mailto', function(e){
                    e.preventDefault();
                    $('body').off('click', '#cancel_mailto');
                    $('body').off('submit', '#mailtoForm');
                    $('#mailto').html('');
                });
                $('body').on('submit', '#mailtoForm', function(e){
                    e.preventDefault();
                    $.ajax({
                        url: base_uri+"send_message/"+ this_id,
                        method: 'post',
                        dataType: "json",
                        data: {
                            email: $(this).find('#mailto-email').val(),
                            text: $(this).find('#mailto-text').val(),
                            captcha: $(this).find('#captcha-key') ? $(this).find('#captcha-key').val() : null
                        },
                        success:function(data){
                            $('#mailto').html(data.content);
                        }
                    });
                });
            },
            error: function(){
                alert('An error occurred');
            }
        })
    });


    /* Показать / скрыть карту */
    $('#toggleMap').click(function(e){
        e.preventDefault();
        if(!initMap){
            show_address($(this).attr('rel'));
            initMap = true;
        }
        $('#showAddress').toggle();
        $(this).text($('#showAddress').is(':visible') ? 'Скрыть карту' : 'Показать карту');
    });

    function show_address(showAddr){
        ymaps.geocode(showAddr, { results: 1 }).then(function (res) {
            var firstGeoObject = res.geoObjects.get(0);
            myMap = new ymaps.Map("showAddress", {
                center: firstGeoObject.geometry.getCoordinates(),
                zoom: 15,
                type:"yandex#map",
                behaviors:['default', 'scrollZoom']
            });
            myMap.options.set('scrollZoomSpeed', 2);
            myMap.controls.add("zoomControl");
            myMap.controls.add("mapTools");

            myMap.geoObjects.add(firstGeoObject);
        });
    }

    function reshow_address(showAddr){
        ymaps.geocode(showAddr, { results: 1 }).then(function (res) {
            var firstGeoObject = res.geoObjects.get(0);
            myMap.setCenter(firstGeoObject.geometry.getCoordinates());
            myMap.geoObjects.add(firstGeoObject);
        });
    }
});