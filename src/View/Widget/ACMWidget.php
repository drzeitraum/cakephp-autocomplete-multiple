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
