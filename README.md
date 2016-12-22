# ClTree
Данная функция позволяет привести данные, хранящиеся в базе данных в виде шаблона Closure Table, к виду, необходимому для их отрисовки с помощью библиотеки d3.js
Для ее использования, ее придется немного исправить, а именно изменить под себя метод запроса к бд на тот, который вы используете
Она возвращает массив, который затем необходимо провести через json_encode, и записать его в файл, с которого d3.js сможет его считать

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
