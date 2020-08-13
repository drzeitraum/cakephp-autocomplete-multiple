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
