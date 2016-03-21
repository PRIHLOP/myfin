<?php

$dropbox_email = '';
$dropbox_password = '';
$dropbox_path = '/backups/myfin';
$folder_for_tmp_files = sys_get_temp_dir();

require_once 'DropboxUploader.php';
require_once '../app/init.php';

/* backup the db OR just a table */
function backup_tables( $tables = '*' )
{

    //get all of the tables
    if($tables == '*')
        $tables = Db::selectGetVerticalArray('SHOW TABLES');
    else
        $tables = is_array($tables) ? $tables : explode(',',$tables);

    $return = '';

    //cycle through
    foreach($tables as $table)
    {
        $result = Db::justQuery('SELECT * FROM ' . $table);
        $num_fields = mysql_num_fields($result);

        $return.= 'DROP TABLE '.$table.';';
        $row2 = mysql_fetch_row(Db::justQuery('SHOW CREATE TABLE ' . $table));
        $return.= "\n\n".$row2[1].";\n\n";

        for ($i = 0; $i < $num_fields; $i++)
        {
            while($row = mysql_fetch_row($result))
            {
                $return.= 'INSERT INTO '.$table.' VALUES(';
                for($j=0; $j<$num_fields; $j++)
                {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = ereg_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                }
                $return.= ");\n";
            }
        }
        $return.="\n\n\n";
    }

    return $return;
}

//$filename = tempnam(sys_get_temp_dir(), 'myfin_');

$filename = $folder_for_tmp_files . '/' . date('d_m_Y__H_i_s') . '.sql.gz';

$gz = gzopen($filename, 'w9');
gzwrite($gz, backup_tables ( array_keys(get_config( 'db_table' )) ));
gzclose($gz);

$dropbox = new DropboxUploader($dropbox_email, $dropbox_password);
$dropbox->setCaCertificateFile("dropbox.cer");
$dropbox->upload($filename, $dropbox_path);

unlink($filename);