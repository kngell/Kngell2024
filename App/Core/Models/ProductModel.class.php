<?php

declare(strict_types=1);

class ProductModel extends Model
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getSql() : MainQuery
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('max(column1)', 'columnsx', 'col1', 'count(col1)')
            ->join('table2', ['col2', 'col3'], 'col6')
            ->innerJoin('table3', 'col4')
            ->on('col2', 'column1')
            ->where('col2', 'in', [45, 87, 965])
            ->where('column1', 2, ['col25' => 50, 'col11' => 35], ['columi', 'azer'])
            ->and('columnsx', 'qsdf')
            ->orWhere('col1', 'xxxxx', function ($qb) {
                $qb->where('columnsx', '<=', 1236)
                    ->orWhere(['col1' => 'azer']);
            })
            ->whereIn('col4', [2, 3, 5, 4, 7])
            ->having('col4563', 11)
            ->groupBy('col1', 'col2', ['collt23', 'noColl'])
            ->orderBy('colonne1 DESC', 'colonne2 ASC', 'columnx')
            ->orderBy('col1', 'DESC', 'col4', 'ASC')
            ->orderBy(['col2' => 'ASC', 'col25' => 'DESC', 'col26'])
            ->limit(50)
            ->offset(25)
            ->build();
    }

    // UPDATE <table>
    // SET <column1> = <value1>,
    //         <column2> = <value2>,
    //         …
    // [WHERE <conditions>]
    //     UPDATE Person p
    // SET p.Street = '123 Lamar',
    // p.zip = '78758',
    // p.phone = 5123334444
    // WHERE p.ID = 131542520
    public function refresh() : MainQuery
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->update()
            ->set(['col3', 'val3', 'col4' => 'val4'], ['col5' => 'val5'], 'col1', 'val1', 'col2', 'val2', )
            ->where('col1', 12354)
            ->build();
    }

    //  INSERT INTO client (prenom, nom, ville, age)
    //  VALUES
    //  ('Rébecca', 'Armand', 'Saint-Didier-des-Bois', 24),
    //  ('Aimée', 'Hebert', 'Marigny-le-Châtel', 36),
    //  ('Marielle', 'Ribeiro', 'Maillères', 27),
    //  ('Hilaire', 'Savary', 'Conie-Molitard', 58);
    public function create() : MainQuery
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->insert()
            ->into('table1')
            ->fields('prenom', 'nom', 'ville', 'age')
            ->values('Rébecca', 'Armand', 'Saint-Didier-des-Bois', 24)
            ->values('Aimée', 'Hebert', 'Marigny-le-Châtel', 36)
            ->values('Marielle', 'Ribeiro', 'Maillères', 27)
            ->values('Hilaire', 'Savary', 'Conie-Molitard', 58)
            ->build();
    }

    // DELETE FROM products
    // WHERE category_id = 50
    // AND product_name <> 'Pear';
    public function del() : MainQuery
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->delete()
            ->from('products')
            ->where(['category_id' => 50])
            ->and('product_name', ' <>', 'Pear')
            ->build();
    }

    public function findAll() : array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $sql = $qb->select()->build()->getQuery();
        $stmt = $this->getEntityManager()->getConnection()->open()->query($sql);
        return  $stmt->fetchAll();
    }

    public function find(string $id) : array|bool
    {
        $sql = 'SELECT * from product WHERE id=:id';
        $stmt = $this->getEntityManager()->getConnection()->open()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}