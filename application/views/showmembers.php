<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<h1>View/Edit member selection panel</h1>
Use filter to select subset. Click member's <i>edit</i> link to edit/view
details.
<p>Filter by string: <input id="filter" type="text" value=""
	onkeyup="selectRows('memberSelect',1)" />
</p>
<p>
<?php
$numCols = count($memberList[0]);
$tableOpenString = '<table id="memberSelect" class="oddEven">';
for ($i=1; $i<=$numCols; $i++) {
	$tableOpenString .= "<col class=\"col$i\" />";
}
$tmpl = array ( 'table_open'  => $tableOpenString, 'row_start' => '<tr class="odd">' );
$this->table->set_template($tmpl);
echo $this->table->generate($memberList);
?>
</p>
