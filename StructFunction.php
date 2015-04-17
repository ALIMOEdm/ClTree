<?php
function getStructure($clientsid){
	global $modx;
		$query =" SELECT SCT.`descendant`, SCT.`depth`, 0 as ancestor FROM ".$modx->getFullTableName("gc_struct_closure_table")." AS SCT ".
				" WHERE SCT.`ancestor`=".$clientsid." AND SCT.`depth` = 0".
				" UNION ALL SELECT SCT.`descendant`, SCT.`depth`, Parent.`ancestor` as ancestor FROM ".$modx->getFullTableName("gc_struct_closure_table")." AS SCT ".
				" LEFT JOIN ".$modx->getFullTableName("gc_struct_closure_table")." AS Parent ON Parent.`descendant`=SCT.`descendant` ".
				" WHERE SCT.`ancestor`=".$clientsid." AND Parent.depth = 1 " ;
	$res = $modx->db->query($query);
	$golden_club_tree = array();
	$parents = array();
	$cnt_depth = 0;
	while ($row = $modx->db->getRow($res)) {
	    //запишем в массив предков текущего клиента
	    $parents[$row["descendant"]] = $row["ancestor"];
	    $depth = $row["depth"];
	
	    //если у нас первый уровень(т е нулевой) создаем корень дерева
	    if(!$depth){
	    	if($cnt_depth){
                    continue;
                }
                $cnt_depth++;
	        $t = array(
	            "id" => $row["descendant"],
	            "name" => $row["fullname"],
	            "children" => array()
	        );
	        $golden_club_tree[] = $t;
	        continue;
	    }
	    //создаем дерево
	    $tree = &$golden_club_tree[0];
	
	    //сохраняем значения глубины
	    $depth_2 = $row["depth"];
	    //и родителя
	    $cur_p = $row["descendant"];
	    $parent_cur_p = $parents[$row["descendant"]];
	
	    $flag_second_depth = false;
	    $parents_stack = array();
	    //в чем суть, поскольку формально все осуществляется в один проход
	    //поэтому у нас 2 вложенных цикла, внутренний отвечает за то, чтобы в массиве предков найти первого в структуре
	    //внешний цикл - берет нужный кусок дерева, относительно которого мы рассматриваем, и таким образом,
	    //беря текущий кусок дерева и ищая на этом участке предка,
	    //мы дохзодим до того места, куда необходимо вставить новый элемент в дерево
	    while($depth > 0){
	
	        if(!$flag_second_depth){
	            if($depth_2 > 0){
	                while($depth_2 > 0){
	                    $cur_p = $parents[$cur_p];
	                    $parents_stack[] = $cur_p;
	                    $depth_2--;
	                }
	                array_pop($parents_stack);
	            }
	            else{
	                $cur_p = $parent_cur_p;
	            }
	            $flag_second_depth = true;
	        }
	        else{
	            $cur_p = array_pop($parents_stack);
	        }
	        $index = 0;
	        $depth--;
				
	        //если мы дошли до корня дерева...то сохраняем ссылку на него
	        if($tree["id"] == $cur_p){
	            $tree = &$tree["children"];
	        }
	        else
	        {
	            //если у нас есть массив, то нам нужно найти предка клиента на данном уровне глубины
	            //и взять его поддерево
	            if(count($tree)){
	                foreach ($tree as $k => $v) {
	                    if ($v["id"] == $cur_p) {
	                        $index = $k;
	                        break;
	                    }
	                }
	                $tree = &$tree[$index]["children"];
	
	            }else{
	                $tree = &$tree;
	            }
	        }
	    }
	    //добавляем информацию о текущем выбранном клиенте к его родителю
	    $tt = array(
	        "id" => $row["descendant"],
	        "name" => $row["fullname"],
	        "children" => array()
	    );
	    $tree[] = $tt;
	
	}
	return array_pop($golden_club_tree);
}
