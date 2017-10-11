<?php
/*
分页处理类 
by zx 2010-6-2
*/
class pagination{
	//分页条
	static function bar($pageCount, $page_s_n = 3, $page = 1) { 
		if($pageCount<=$page) $page = $pageCount; 
		if($page<1) $page=1;
		$page_s_n++;
		$pageString = array ();
		$pageString ['now'] = $page;
		for($i = 1; $i < $page_s_n; $i ++) {
			$n = $page - $i;
			if ($n > 0) {
				array_unshift ( $pageString, $n );
			}
			
			$n = $page + $i;
			if ($n <= $pageCount) {
				$pageString [] = $n;
			}
		} 
		

		if ($page > $page_s_n+1) {
			array_unshift ( $pageString, "..." );
		}

		if ($page > $page_s_n) {
			array_unshift ( $pageString, "1" );
		}
		
		if ($page <= $pageCount - $page_s_n-1) {
			$pageString [] = "...";
		}
		if ($page <= $pageCount - $page_s_n) {
			$pageString [] = $pageCount;
		}
		
		$n = $page - 1;
		if ($n > 0) {
			array_unshift ( $pageString, $n );
		} else {
			array_unshift ( $pageString, "now_first" );
		}
		$n = $page + 1;
		if ($n <= $pageCount) {
			$pageString ['next'] = $n;
		} else {
			$pageString ['next'] = "none_next";
		}
		return $pageString;
	}
}
?>