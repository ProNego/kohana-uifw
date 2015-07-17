<?php defined('SYSPATH') or die('No direct script access.'); ?>
<script type="text/javascript">
    var select2_initialized;

    jQuery(document).ready(function() {
        if (select2_initialized == true)
        {
            return;
        }
        select2_initialized = true;

        jQuery(".select2").select2({
            <?php foreach ($options AS $key => $val): ?>
                "<?php echo $key; ?>": <?php echo $val; ?>,
            <?php endforeach; ?>
        });
    });
</script>

<select <?php echo (isset($html_id) && !empty($html_id)) ? 'id="'.$html_id.'"' : ''; ?> class="select2">
    <?php foreach($data AS $key => $value): ?>
        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
    <?php endforeach; ?>
</select>