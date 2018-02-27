function getDataAttributes(el) {
    var data = {};
    [].forEach.call(el.attributes, function (attr) {
        if (/^data-/.test(attr.name)) {
            var camelCaseName = attr.name.substr(5).replace(/-(.)/g, function ($0, $1) {
                return $1.toUpperCase();
            });
            data[camelCaseName] = attr.value;
        }
    });
    return data;
}

jQuery(document).ready(function ($) {
    $('ul#elements').sortable();
    $('#create-new-form button#add-new').click(function (event) {
        event.preventDefault();
        //alert("hello");
        var input_type = $(this).closest('form').find("#new-input-type").val();
        //alert(input_type);
        var count = $("ul#elements li").length;
        //alert(count);
        if (input_type == "text") {
            var new_element = '<li data-item="text" data-label="">';
            new_element += '<h4>' + input_type + ' Field</h4>';
            new_element += '<p><label>Label </label><input type="text" name="label" value="" required/></p>';
            //new_element += '<span><a title="Remove This Element" class="remove">X</a></span>';
            new_element += '</li>';
        } else if (input_type == "select") {
            var new_element = '<li data-item="select" data-label="" data-values="">';
            new_element += '<h4>' + input_type + ' Field</h4>';
            new_element += '<p><label>Label </label><input type="text" name="label" value="" required/></p>';
            new_element += '<p><label>Values </label><input type="text" name="values" value="" required/></p>';
            //new_element += '<span><a title="Remove This Element" class="remove">X</a></span>';
            new_element += '</li>';
        } else if (input_type == "radio") {
            var new_element = '<li data-item="radio" data-label="" data-values="">';
            new_element += '<h4>' + input_type + ' Buttons</h4>';
            new_element += '<p><label>Label </label><input type="text" name="label" value="" required/></p>';
            new_element += '<p><label>Values </label><input type="text" name="values" value="" required/></p>';
            //new_element += '<span><a title="Remove This Element" class="remove">X</a></span>';
            new_element += '</li>';
        } else {
        }
        $("ul#elements").append(new_element).show('slow');
    });

    $('#submit-data input#submit').click(function (e) {
        //e.preventDefault();
        var inputData = [];
        //Iterate through ul li
        $("ul#elements li").each(function () {

            var $inputs = $(this).find('input');
            var values = {};
            //iterate through input
            $inputs.each(function () {
                //alert($(this).val());
                //$(this).parent("li").addClass($(this).val()); .data('myval');
                var data_key = "data-" + $(this).attr("name");
                $(this).closest("li").attr(data_key, $(this).val());
                //Verify value
                //console.log("Name is "+ $(this).attr("name") + " & value is " + $(this).val());
            });

            var data = getDataAttributes(this);
            inputData.push(data);

        });

        $('#woo_reg_customizer').val(JSON.stringify(inputData));

        //console.log(inputData);



    });
    $('ul#elements li a.remove').click(function (e) {
        //alert('remove');
        var item_remove = $(this).closest("li");
        $(item_remove).slideUp(150, function () {
            $(item_remove).remove();
        });

    });

});


