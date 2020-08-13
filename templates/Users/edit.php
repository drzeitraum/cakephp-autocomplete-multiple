<?= $this->Form->create($user) ?>
<div class="form-group">
    <?= $this->Form->control('countries._ids', ['label' => 'Countries', 'type' => 'acm', 'options' => $countries, 'default' => $user->transfer_materials]) ?>
</div>
<?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
<?= $this->Form->end() ?>
