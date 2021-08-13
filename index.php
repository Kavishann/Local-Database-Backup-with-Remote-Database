<?php
require('config.php');
class DB {
   public function Connect($method=1){

  try {
    if ($method==1) {
    $dsn = DBA_TYPE.':host=' . DBA_HOST . ';dbname=' . DBA_NAME;
    $pdo = new PDO ($dsn, DBA_USERNAME, DBA_PASSWORD);
    }else{
    $dsn = DBB_TYPE.':host=' . DBB_HOST . ';dbname=' . DBB_NAME;
    $pdo = new PDO ($dsn, DBB_USERNAME, DBB_PASSWORD);    
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
    return $pdo;
    }   
    catch ( PDOException $e ) {
     echo "Connection failed: ".$e->getMessage();
    } 

   }

}
$DbA = new DB();
$DbB = new DB();
$DbA = $DbA->Connect(); //Get Database A
$DbB =  $DbB->Connect(2); // Get Database B
if (!empty(TABLES)) {
   foreach(TABLES as $stable){
   $last_record= table_last_id($stable);
   
$sql = 'SELECT * from '.$stable.' WHERE id > '.$last_record;
$stmt = $DbA->prepare($sql);
$stmt->execute();
if($stmt->rowCount() > 0){
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$results_last_record = end($results);
$results_last_id = $results_last_record['id'];
    foreach($results as $single_record){
foreach ($single_record as $column => $value) {
        $cols[] = $column;
        $vals[] = $value;
    }
$colnames = "`".implode("`, `", $cols)."`";
    $colvals = "'".implode("', '", $vals)."'";
   $sql='INSERT INTO '.$stable.' ('.$colnames.')
  VALUES ('.$colvals.')';
 $DbB->exec($sql);
 update_table($stable,$DbB->lastInsertId());
unset($cols);
unset($vals);
}
}
}
}else{
    echo "Please add least one table in config.php";
}
function table_last_id($table_name){
$DbA = new DB();
$DbA = $DbA->Connect();
$sql = 'SELECT lastid from kbsync WHERE name="'.$table_name.'"';
$stmt = $DbA->prepare($sql);
$stmt->execute();
if($stmt->rowCount() > 0){
    $row=$stmt->fetchAll(PDO::FETCH_ASSOC);

 return $row[0]["lastid"];
}else{
    return 0;
}
}
function update_table($table_name,$last_id){
$DbA = new DB();
$DbA = $DbA->Connect();
$sql = 'SELECT * from kbsync WHERE name="'.$table_name.'"';
$stmt = $DbA->prepare($sql);
$stmt->execute();
if($stmt->rowCount() > 0){
    $sql='UPDATE kbsync SET lastid='.$last_id.' WHERE name = "'.$table_name.'"';
}else{
     $sql='INSERT INTO kbsync (name,lastid) VALUES ("'.$table_name.'",'.$last_id.')';
}
$DbA->exec($sql);
return true;
} 

?>

 