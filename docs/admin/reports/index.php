<?php
if (!$user || !$user['is_admin']) {
	$core->redirect('/login/');
}

list($report_id) = $params;
?>

<h2>Reports</h2>

<?php
if ($report_id) :

    $sql = sprintf("SELECT * FROM reports WHERE id = %s", addslashes($report_id));
    $report = $db->select_one($sql);
    
    $sql = $report['query'];
    $data = $db->select($sql);
    
    if ($data) :
        ?>
        
        <table width="100%">
        <thead>
        <tr>
            
        <?php
        $cols = array_keys($data[0]);
        foreach ($cols as $col) :
            ?>
            
            <th><?php echo $col; ?></th>
            
            <?php
        endforeach;
        ?>
        
        </tr>
        </thead>
        <tbody>        

        <?php
        foreach ($data as $row) :
            echo '<tr>';
            foreach ($row as $field) :
                ?>
                
                <td><?php echo $field; ?></td>
                
                <?php
            endforeach;
        endforeach;
        ?>
        
        </tbody>
        </table>
        
        <?php    
    else :
        echo 'No data.';
    endif;

else :

    $sql = "SELECT * FROM reports";
    $reports = $db->select($sql);
    
    echo '<ul>';
    
    foreach ($reports as $report) :
        ?>
        
        <li>
            <a href="/admin/reports/<?php echo $report['id']; ?>/"><?php echo $report['title']; ?></a>
        </li>
        
        <?php
    endforeach;
    
    echo '</ul>';
    
endif;
?>