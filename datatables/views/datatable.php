<?php defined('SYSPATH') or die('No direct script access.');
?>

<script type="text/javascript">
    var datatables_initialized;

    jQuery(document).ready(function() {
        if (datatables_initialized == true)
        {
            return;
        }
        datatables_initialized = true;

        $('.datatable').DataTable({
            "pageLength": <?php echo $page_length; ?>,
            <?php if (isset($serverside) && isset($ajax_url) && $serverside): ?>
			"processing": true,
			"serverSide": true,
			"ajax": "<?php echo $ajax_url; ?>",
            <?php endif; ?>
            
            <?php foreach ($disabled_features AS $feature): ?>
            "<?php echo $feature; ?>": false,
            <?php endforeach; ?>
            <?php foreach ($options AS $key => $val): ?>
            "<?php echo $key; ?>": <?php echo (is_array($val)) ? 'jQuery.parseJSON("'.json_encode($val).'")' : $val; ?>,
            <?php endforeach; ?>
        });
    } );
</script>

<table <?php echo (isset($html_id) && !empty($html_id)) ? 'id="'.$html_id.'"' : ''; ?> class="datatable">
    <thead>
        <tr>
            <?php foreach ($columns AS $c): ?>
                <th><?php echo $c; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <?php foreach ($columns AS $c): ?>
                <th><?php echo $c; ?></th>
            <?php endforeach; ?>
        </tr>
    </tfoot>
    <?php if (!isset($serverside) || !isset($ajax_url) && !$serverside): ?>
    <tbody>
        <?php foreach ($entries AS $entry): ?>
        <tr>
            <?php foreach ($entry AS $t): ?>
                <td><?php echo $t; ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <?php endif; ?>
</table>