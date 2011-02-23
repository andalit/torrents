<?php if (!defined('BB_ROOT') OR empty($db)) die(basename(__FILE__)); ?>

<style type="text/css">
.sqlLog {
	clear: both;
	font-family: Courier, monospace;
	font-size: 12px;
	white-space: nowrap;
	background: #F5F5F5;
	border: 1px solid #BBC0C8;
	overflow: auto;
	width: 98%;
	max-height: 400px;
	margin: 0 auto;
	padding: 2px 4px;
}
.sqlLogTitle {
	font-weight: bold;
	color: #444444;
	font-size: 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	padding-bottom: 2px;
}
.sqlLogRow {
	background-color: #F5F5F5;
	padding-bottom: 1px;
	border: solid #F5F5F5;
	border-width: 0px 0px 1px 0px;
	cursor: pointer;
}
.sqlLogHead {
	text-align: right;
	float: right;
	width: 100%;
}
.sqlLogHead fieldset {
	float: right;
	margin-right: 4px;
}
.sqlLogWrapped {
	white-space: normal;
	overflow: visible;
}
.sqlExplain {
	color: #B50000;
	font-size: 13px;
	cursor: default;
}
.sqlHover {
	border-color: #8B0000;
}
.sqlHighlight {
	background: #FFE4E1;
}
</style>