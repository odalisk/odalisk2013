<?php
namespace OdaliskProject\Bundle\Repository;

use Doctrine\ORM\EntityRepository;


class PortalRepository extends EntityRepository
{
    public function deleteByPortalName($name)
    {
        
    }
    
    public function getPortalsMatching($criterias, $page_index, $page_size) {
        $qb = $this->createQueryBuilder('p');
        if(array_key_exists('in', $criterias)) {
            // JOIN ... WITH IN (...)
            $join = 0;
            foreach($criterias['in'] as $column => $values) {
                $qb->join('p.' . $column, 'j' . $join, 'WITH', $qb->expr()->in('j' . $join . '.id', $values));
                $join++;
            }
        }
        
        if(array_key_exists('where', $criterias)) {
            // WHERE clause
            $cond = 0;
            $parameters = array();
            foreach($criterias['where'] as $condition) {
                if(0 == $cond) {
                    $qb->where('p.' . $condition[0] . ' ' . $condition[1] . ' :p' . $cond);
                } else {
                    $qb->andWhere('p.' . $condition[0] . ' ' . $condition[1] . ' :p' . $cond);
                }

                $parameters['p' . $cond] = $condition[2];
                $cond++;
            }
            $qb->setParameters($parameters);
        }
        
        $qb->orderBy('p.id', 'ASC');
        $qb->setFirstResult($page_index * $page_size);
        $qb->setMaxResults($page_size);
        
        return $qb->getQuery()->getResult();
    }
    
    public function getCategories($id)
    {
        $sth = $this->getEntityManager()
            ->getConnection()
            ->prepare('
                SELECT DISTINCT c.id, c.category
                FROM categories c
                    JOIN dataset_category dc on c.id = dc.category_id
                    JOIN datasets d ON (d.id = dc.dataset_id AND d.portal_id = :portal_id)
            ');
        $sth->execute(array('portal_id' => $id));
        
        return $sth->fetchAll();
    }
    
    public function getLicenses($id)
    {
        $sth = $this->getEntityManager()
            ->getConnection()
            ->prepare('
                SELECT DISTINCT l.id, l.name
                FROM licenses l
                    JOIN datasets d ON (d.license_id = l.id AND d.portal_id = :portal_id)
            ');
        $sth->execute(array('portal_id' => $id));
        
        return $sth->fetchAll();
    }
    
    public function getFormats($id)
    {
        $sth = $this->getEntityManager()
            ->getConnection()
            ->prepare('
                SELECT DISTINCT f.id, f.format
                FROM formats f
                    JOIN dataset_format df on f.id = df.format_id
                    JOIN datasets d ON (d.id = df.dataset_id AND d.portal_id = :portal_id)
            ');
        $sth->execute(array('portal_id' => $id));
        
        return $sth->fetchAll();
    }
    
    public function getPortalCountries()
    {
        $data = $this->getEntityManager()
            ->createQuery('
                SELECT DISTINCT p.country 
                FROM OdaliskProject\Bundle\Entity\Portal p
                ORDER BY p.country ASC')
            ->getResult();

        $result = array();
        foreach($data as $row) {
            $result[] = $row['country'];
        }
        return $result;
    }

    public function getPortalEntities()
    {
        $data = $this->getEntityManager()
            ->createQuery('
                SELECT DISTINCT p.entity 
                FROM OdaliskProject\Bundle\Entity\Portal p
                ORDER BY p.entity ASC')
            ->getResult();

        $result = array();
        foreach($data as $row) {
            $result[] = $row['entity'];
        }
        return $result;
    }

    public function getPortalStatuses()
    {
        $data = $this->getEntityManager()
            ->createQuery('
                SELECT DISTINCT p.status 
                FROM OdaliskProject\Bundle\Entity\Portal p
                ORDER BY p.status ASC')
            ->getResult();

        $result = array();
        foreach($data as $row) {
            $result[] = $row['status'];
        }
        return $result;
    }
    
    public function getPortalTypes()
    {
        $data = $this->getEntityManager()
            ->createQuery('
                SELECT DISTINCT p.type 
                FROM OdaliskProject\Bundle\Entity\Portal p
                ORDER BY p.type ASC')
            ->getResult();

        $result = array();
        foreach($data as $row) {
            $result[] = $row['type'];
        }
        return $result;
    }

    public function getDatasetsCount($portal)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT count(d) FROM OdaliskProject\Bundle\Entity\Dataset d WHERE d.portal = :portal')
            ->setParameter('portal', $portal)
            ->getSingleScalarResult();

    }


    public function getInChargePersonCount($portal)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT count(d) FROM OdaliskProject\Bundle\Entity\Dataset d WHERE d.portal = :portal and (d.owner is not null or d.maintainer is not null or d.provider is not null)')
            ->setParameter('portal', $portal)
            ->getSingleScalarResult();

    }

    public function getReleasedOnExistCount($portal)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT count(d) FROM OdaliskProject\Bundle\Entity\Dataset d WHERE d.portal = :portal and d.released_on is not null')
            ->setParameter('portal', $portal)
            ->getSingleScalarResult();

    }

    public function getLastUpdatedOnExistCount($portal)
    {

        return $this->getEntityManager()
            ->createQuery('SELECT count(d) FROM OdaliskProject\Bundle\Entity\Dataset d WHERE d.portal = :portal and d.last_updated_on is not null')
            ->setParameter('portal', $portal)
            ->getSingleScalarResult();
    }

    public function getCategoryExistCount($portal)
    {

        $stmt = $this->getEntityManager()
            ->getConnection()
            ->prepare("
                    SELECT count(*) FROM datasets WHERE portal_id = ".$portal->getId()." and id IN (SELECT DISTINCT dataset_id FROM dataset_category)"
                    );

        return $stmt->execute();

    }

    public function getSummaryAndTitleAtLeastCount($portal)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT count(d) FROM OdaliskProject\Bundle\Entity\Dataset d WHERE d.portal = :portal and d.name is not null and d.summary is not null')
            ->setParameter('portal', $portal)
            ->getSingleScalarResult();

    }

    public function getLicenseCount($portal)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT count(d) FROM OdaliskProject\Bundle\Entity\Dataset d WHERE d.portal = :portal and d.license is not null')
            ->setParameter('portal', $portal)
            ->getSingleScalarResult();

    }

    public function getFormatDistribution($portal){

        $stmt = $this->getEntityManager()
            ->getConnection()
            ->prepare('SELECT format, COUNT(*) FROM ( SELECT id FROM `datasets` WHERE portal_id = ? ) as d JOIN  `dataset_format` ON d.id = dataset_id, formats WHERE  `formats`.id =  `format_id` GROUP BY format'
                    );

        $stmt->bindValue(1, $portal->getId());
        $stmt->execute();
        $res = $stmt->fetchAll();

        $output = array();
        foreach ($res as $key => $value) {
            $output[$value['format']] = $value['COUNT(*)'];
        }

        

        return $output;
    }

    public function getCategoryDistribution($portal){

        $stmt = $this->getEntityManager()
            ->getConnection()
            ->prepare('SELECT category, COUNT(*) FROM ( SELECT id FROM `datasets` WHERE portal_id = :portal_id ) as d JOIN dataset_category on `dataset_id` = d.id, categories WHERE `category_id` = categories.id
                    group by category'
                    );

        $stmt->bindValue("portal_id", $portal->getId());
        $stmt->execute();
        $res = $stmt->fetchAll();

        $output = array();
        foreach ($res as $key => $value) {
            $output[$value['category']] = $value['COUNT(*)'];
        }

        return $output;
    }

    public function getLicenseDistribution($portal){

        $stmt = $this->getEntityManager()
            ->getConnection()
            ->prepare('SELECT licenses.name, COUNT(*)
            FROM datasets JOIN licenses ON datasets.license_id = licenses.id
            WHERE datasets.portal_id = :portal_id
            GROUP BY licenses.name;');
        $stmt->execute(array('portal_id' => $portal->getId()));
        $res = $stmt->fetchAll();

        $output = array();

        foreach ($res as $key => $value) {
            $output[$value['name']] = $value['COUNT(*)'];
        }
        
        return $output;
    }

    
    public function agregateDatasetsStats($portal) {
    }

}
