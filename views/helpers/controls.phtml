<?php
    defined('ABP01_LOADED') or die;
?>

<?php function extractValueFromData($data, $field) {
        if ($data->tourInfo && isset($data->tourInfo[$field])) {
            return $data->tourInfo[$field];
        } else {
            return null;
        }
} ?>

<?php function renderDifficultyLevelOptions(array $difficultyLevels, $selected) { ?>
    <?php foreach ($difficultyLevels as $option): ?>
        <option value="<?php echo $option->id ?>" <?php echo ($selected == $option->id ? 'selected="selected"' : ''); ?>><?php echo __($option->label) ?></option>
    <?php endforeach; ?>
<?php } ?>

<?php function renderCheckboxOption($option, $fieldName, $selected) { ?>
    <?php $id = 'ctrl_abp01_' . $fieldName . '_' . $option->id; ?>
    <span class="abp01-optionContainer">
        <input type="checkbox" name="<?php echo $fieldName ?>[]" id="<?php echo $id; ?>" value="<?php echo $option->id; ?>"
            <?php echo ($selected == $option->id || (is_array($selected) && in_array($option->id, $selected))
                ? 'checked="checked"' : ''); ?> />
        <label for="<?php echo $id; ?>" class="abp01-option-label"><?php echo __($option->label);?></label>
    </span>
<?php } ?>

<?php function renderCheckboxOptions(array $options, $fieldName, $data) { ?>
    <?php $selected = extractValueFromData($data, $fieldName); ?>
    <?php foreach ($options as $option): ?>
        <?php renderCheckboxOption($option, $fieldName, $selected); ?>
    <?php endforeach; ?>
<?php } ?>