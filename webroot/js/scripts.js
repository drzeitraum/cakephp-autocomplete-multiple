/**
 * autoCompleteMulti - mini plugin for Auto complete multiple inputs in CakePHP

 * @author Vyacheslav K. <vyach.kotlyarov@gmail.com>
 * @link https://github.com/drzeitraum/cakephp-autocomplete-multiple
 */

(function ($) {

    jQuery.fn.autoCompleteMulti = function (opt) {

        // vars
        opt = $.extend({
            elem: ".auto-complete-multi",
            name: ""
        }, opt);

        // styles, sizes
        var styles = function (num = 0) {

            var count = $(".acm [id*='_texts'] a").length + num;
            if (count) {
                var height = count * $('.acm-text').outerHeight();
                var h = height + 50 + 'px';
                var p = height + 'px';
            } else {
                var h = 'inherit';
                var p = '0.375rem';
            }
            $(opt.elem).css({
                'height': h,
                'padding-top': p
            });
        };

        // searching
        var make = function () {

            $('form').unbind("keyup").on('keyup', opt.elem, function () {

                var attr = $(this).attr('id'); // this attr id
                var search = $(this).val(); // search word
                var where = $(this).attr('name'); // where search

                // array active lists
                var ids = [];
                var getChild = $(".acm [id*='_texts']").children();
                getChild.each(function (i, v) {
                    ids.push($(v).attr('id'))
                });

                // request
                $.ajax({
                    url: '/cakephp-autocomplete-multiple/autocomplete/', //change this path to the name of your Auto complete controller
                    data: ({
                        search: search,
                        where: where,
                        ids: ids
                    }),
                    success: function (response) {
                        $("#" + where + "_result").html(response); // print result
                        // chose list
                        $('form').unbind("click").on('click', '.ac-list li', function () {
                            var id = $(this).attr('id');
                            if (id !== 'not_found') {
                                $('.ac-list').addClass('ac-none'); // hide ul
                                $('#' + attr).val('');  // insert name
                                $("#" + where + "_texts").append('<a class="acm-text" id="' + id + '" href="#">' + $(this).text() + ' &#10006;</a>');
                                $("#" + where + "_values").append('<input type="hidden" name="' + where.toLowerCase() + '[_ids][]" id="' + attr + '-' + id + '" value="' + id + '">');
                                styles();
                            }
                        });
                    }
                });
            });
        };

        // remove list
        var remove = function () {
            $('body')
                .on('click', '.acm-text', function (e) {
                    e.preventDefault();
                    $('#' + opt.name + '-' + $(this).attr('id')).remove();
                    $(this).remove();
                    styles();
                })
                .click(function () {
                    $('.ac-list').addClass('ac-none');
                });
        };

        // returns all method
        if (opt.elem.length > 0) {
            return this.each(function () {
                styles(0);
                make();
                remove();
            })
        }

    }

})(jQuery);

// init
$('.auto-complete-multi').autoCompleteMulti({
    name: 'countries-ids'
});
