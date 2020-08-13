# CakePHP 4.x autocomplete multiple input ([DEMO](https://kotlyarov.us/cakephp-autocomplete-multiple/edit/1))
This is a simple example: how to create autocomplete multiple input using widget and controller in CakePHP 4.x

#### Tables for `users`, `countries`, `users_countries`

```mysql
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` char(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `countries` (
  `id` int(10) NOT NULL,
  `name` char(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE `users_countries` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `country_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

```

#### Create new widget file `ACMWidget.php` in `/src/View/Widget/`

```php
<?
namespace App\View\Widget;

use Cake\View\Form\ContextInterface;
use Cake\View\Widget\WidgetInterface;

/**
 * ACMWidget - autocomplete multiple
 */
class ACMWidget implements WidgetInterface
{

    protected $_templates;

    public function __construct($templates)
    {
        $this->_templates = $templates;
    }

    /**
     * Cake\View\Widget\MultiCheckboxWidget::_isSelected
     */
    protected function _isSelected($key, $selected)
    {
        if ($selected === null) {
            return false;
        }
        $isArray = is_array($selected);
        if (!$isArray) {
            return (string)$key === (string)$selected;
        }
        $strict = !is_numeric($key);

        return in_array((string)$key, $selected, $strict);
    }

    public function render(array $data, ContextInterface $context): string
    {

        //prefix template
        $acm[] = $this->_templates->format('acm_prefix', [
            'name' => $data['name']
        ]);

        // active items
        foreach ($data['options']->toArray() as $key => $value) {
            // if using Modelless Forms, need to rebuild $data['val'] for checking method _isSelected
            if ($this->_isSelected($key,  $data['val'])) {
                $texts[] = '<a class="acm-text" id="' . $key . '" href="#">' . $value . ' &#10006;</a>';
                $values[] = '<input type="hidden" name="' . $data['name'] . '[]' . '" id="' . $data['id'] . '-' . $key . '" value="' . $key . '">';
            }
        }

        $acm[] = $this->_templates->format('acm', [
            'texts' => isset($texts) ? $texts : '',
            'values' => isset($values) ? $values : '',
            'name' => $data['name'],
            'id' => $data['id'],
            'val' => $data['val'] ? $data['val'] : '', // value id list
            'where' => $data['options']->getRepository()->getAlias() // table
        ]);

        return implode('', $acm);

    }

    public function secureFields(array $data): array
    {
        return [
            $data['name']
        ];
    }
}

```

#### Create template `tpl-form.php` for FormHelper in `/config/` or include in your file

```php
<?php
return [
    // autocomplete multiple
    'acm_prefix' => '<input type="hidden" name="{{name}}" />',
    'acm' => '<input type="text" id="{{id}}" name="{{where}}" class="auto-complete-multi" autocomplete="off" /><span id="{{where}}_texts">{{texts}}</span><span id="{{where}}_values">{{values}}</span><span id="{{where}}_result"></span>'
];

```

#### Include custom template for form helper and our widget in `src/View/AppView.php`

```php
<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 */
namespace App\View;

use Cake\View\View;

/**
 * Application View
 */
class AppView extends View
{
    /**
     * Initialization hook method.
     */
    public function initialize(): void
    {
        $this->loadHelper('Form',
            [
                'templates' => 'tpl-form',
                'widgets' => [
                    'acm' => ['ACM'],
                ]
            ]
        );
    }
}

```

#### Create controller `AutocompleteController.php` in `/src/Controller/`

```php
<?php
declare(strict_types = 1);

namespace App\Controller;

/**
 * Autocomplete Controller
 */
class AutocompleteController extends AppController
{

    /**
     * initialize
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel($this->getRequest()->getQuery('where'));
    }

    /**
     * Index method
     */
    public function index()
    {
        $results = $conditions = [];

        if ($this->getRequest()->getAttribute('isAjax')) {
            if ($this->getRequest()->getQuery('ids')) {
                $conditions = ['id NOT IN' => $this->getRequest()->getQuery('ids')];
            }
            $results = $this->{$this->getRequest()->getQuery('where')}->find('all')
                ->where([
                    'name LIKE' => '%' . $this->getRequest()->getQuery('search') . '%',
                    $conditions
                ])
                ->limit(10)
                ->toArray();
        }

        $this->set(compact('results'));

    }

}

```

#### Create view `index.php` for controller Autocomplete in `/templates/Autocomplete/`

```html
<ul class='ac-list'>
    <? if (count($results)) { ?>
        <? foreach ($results as $result) { ?>
            <li id='<?= $result->id ?>'><?= $this->Base->illumination($_REQUEST['search'], $result->name) ?></li>
        <? } ?>
    <? } else { ?>
        <li id='ac_not_found'>At your request <b><?= $_REQUEST['search'] ?></b> nothing found</li>
    <? } ?>
</ul>

```

#### Add template `edit.php` for users edit action in `/templates/Users/`

```php
<?= $this->Form->create($user) ?>
<?= $this->Form->control('countries._ids', ['label' => 'Countries', 'type' => 'acm', 'options' => $countries, 'default' => $user->transfer_materials]) ?>
<?= $this->Form->button(__('Save'), []) ?>
<?= $this->Form->end() ?>

```

#### Include Jquery mini plugin for auto complete multiple inputs or use other solution

```javascript
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

```

#### And style for plugin

```css
.acm {
    position: relative;
}

.acm ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.acm .ac-none {
    position: absolute;
    z-index: 0;
    left: -9999px;
}

.acm .ac-list {
    background: white;
    border-left: 1px solid gray;
    border-right: 1px solid gray;
    margin: 5px 0;
    position: absolute;
    text-align: left;
    z-index: 10000;
    width: 100%;
}

.acm .ac-list li {
    cursor: pointer;
    margin: 0;
    border-bottom: 1px solid gray;
    padding: 0 10px;
}

.acm .ac-list li:hover {
    color: red;
}

.acm .ac-list li#ac_not_found {
    cursor: default;
}

.acm .ac-list li#ac_not_found:hover {
    color: black;
}

.acm [id*="_texts"] {
    position: absolute;
    left: 15px;
    z-index: 999;
    top: 45px;
}

.acm .acm-text {
    display: block;
}

```

#### After setting, the output of our custom fields in views becomes simple:
```php
echo $this->Form->control('<your_item>._ids', ['type' => '<your_widget_name>']
```

#### Thank you for watching and hope it came in handy.