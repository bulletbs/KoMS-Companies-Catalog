$(function(){
    var base_uri = '/shops/';

/**
 * Города показать все или только крупные
 */
    $('#showAllCity').click(function(e){
        e.preventDefault();
        $(this).addClass('active');
        $('#showBigCity').removeClass('active');
        $('#big_city_list').hide();
        $('#all_city_list').show();
    });
    $('#showBigCity').click(function(e){
        e.preventDefault();
        $(this).addClass('active');
        $('#showAllCity').removeClass('active');
        $('#big_city_list').show();
        $('#all_city_list').hide();
    });
    //$('#filtersList').slideDown(500);

/**
 * Regions selector
 */
    $('#regionLabel input').on('click', function(){
        $('.selectorWrapper:visible').hide(0);
        $('#regionsList').show(50);
        addMouseUpEvent('#regionsList');
    });
    $(document).on('click', '#regionLabel li', function(){
        if($(this).data('action') == 'go'){
            $('#regionAlias').val( $(this).data('alias') );
            $('#regionTopInput').val( $(this).data('title') );
            $(this).parents('.selectorWrapper').slideUp(50);
            $('#boardTopForm').submit();
        }
        else if($(this).data('action') == 'back'){
            $('#regionLabel .selectorWrapper:visible').slideUp(50);
            $('#regionLabel .st-level').slideDown(50);
            addMouseUpEvent('#regionsList');
        }
            else{
            $('#regionsList').slideUp(50);
            var regionId = $(this).data('id');
            $.ajax({
                type: "POST",
                url: base_uri+ 'catalogSearch/cities',
                data: {'region_id':$(this).data('id')},
                dataType: 'json',
                success: function(data){
                    $('#regionsList').after(data.content);
                    $('#regionsCities_'+ regionId).slideDown(50);
                    addMouseUpEvent('#regionsCities_'+ regionId);
                }
            });
        }
    });

/**
 * Category selector
 */
    $('#categoryLabel input').on('click', function(){
        $('.selectorWrapper:visible').hide(0);
        $('#categoriesList').show(50);;
        addMouseUpEvent('#categoriesList');
    });
    $('#categoryLabel li').on('click', function(){
        if($(this).data('action') == 'go'){
            $('#categoryAlias').val( $(this).data('alias') );
            $('#categoryTopInput').val( $(this).text() );
            $(this).parents('.selectorWrapper').slideUp(50);
            $('#filtersList').html('');
            $('#boardTopForm').submit();
        }
        else if($(this).data('action') == 'back'){
            $('#categoryLabel .selectorWrapper:visible').slideUp(50);
            $('#categoryLabel .st-level').slideDown(50);
            addMouseUpEvent('#categoriesList');
        }
        else{
            $('#categoriesList').slideUp(50);
            $('#categoriesSubcats_'+ $(this).data('id')).slideDown(50);
            addMouseUpEvent('#categoriesSubcats_'+ $(this).data('id'));
        }
    });

$('#boardTopForm').submit(function(e){
    e.preventDefault();
    $(this).unbind('submit');
    disableEmptyFilters();
    $(this).attr('action', generateFormUri()).submit();
});

/**
 * Click out of list of close button event handler
 * @param target_id
 */
    function addMouseUpEvent(target_id){
        $(document).unbind('mouseup');
        $(document).mouseup(function (e){
            var container = $(target_id);
            if (!container.is(e.target) && container.has(e.target).length === 0){
                $(target_id).hide(50);
                $(document).unbind('mouseup');
            }
        });
    }

/**
 * Form action generator
 * @returns {string}
 */
    function generateFormUri(){
        var uri = base_uri;
        var region = $('#regionAlias').val();
        var category = $('#categoryAlias').val();
        uri += region ? region : '';
        if(category)
            uri += '/'+category;
        if(uri != base_uri)
            uri += '.html';
        return uri;
    }

    /**
     *  Disable empty fields
     */
    function disableEmptyFilters(){
        $('#filtersList input[type=text], #filtersList select').each(function(){
            if( !$(this).val() || $(this).data('main'))
                $(this).attr('disabled', 'disabled');
        });
        if(!$('#serchformQuery').val())
            $('#serchformQuery').attr('disabled', 'disabled');
    }
});