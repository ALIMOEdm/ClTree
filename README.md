# ClTree
The code presented in StructFunction.php allow you create a tree for, for example, d3.js charts with only 1 root.

ClosureTable file contains class with a litle more functionality.
You need to call handleClosure function with result of your query.

Prefer to use ClosureTable

Sample of select query
````php
    /**
     * Get Tree structure for owner level
     *
     * @param array $owner_level
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    function getStructure(array $owner_level){
        if (!count($owner_level)) {
            return array();
        }
        $query =
            "
          SELECT SCT.`descendant`, SCT.`length` as depth, SCT.`ancestor` as ancestor, AOL.* FROM adver_owner_level_closure AS SCT
          LEFT JOIN advert_owner_level AS AOL ON AOL.`id`=SCT.`descendant`
          WHERE SCT.`ancestor` IN (".implode(',', $owner_level).")
        ";

        $connection = $this->getEntityManager()->getConnection();
        $prep = $connection->prepare($query);
        $prep->execute(array());
        $res = $prep->fetchAll(\PDO::FETCH_ASSOC);

        return $res;
    }
````
