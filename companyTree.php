<?php

class Travel
{

    private $id;
    private $price;
    private $companyId;

    public function __construct(
        int $id,
        float $price,
        string $companyId
    )
    {
        $this->id = $id;
        $this->price = $price;
        $this->companyId = $companyId;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompanyId(): string
    {
        return $this->companyId;
    }

    /**
     * @param string $companyId
     */
    public function setCompanyId(string $companyId)
    {
        $this->companyId = $companyId;
        return $this;
    }
}

class Company
{

    private $id;
    private $name;
    private $cost;
    private $children;

    public function __construct(
        string $id,
        string $name,
        float $cost,
        array $children
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->cost = $cost;
        $this->children = $children;
    }

    /**
     * @return string
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return float
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @param float $cost
     */
    public function setCost(float $cost)
    {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param array|null $children
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
        return $this;
    }
}

class TestScript
{
    public function execute()
    {
        $start = microtime(true);
        $companies = file_get_contents("https://5f27781bf5d27e001612e057.mockapi.io/webprovise/companies", true);
        $companies = json_decode($companies, true);
        $travels = file_get_contents('https://5f27781bf5d27e001612e057.mockapi.io/webprovise/travels', true);
        $travels = json_decode($travels, true);

        $travelsCostByCompanyId = [];
        foreach ($travels as $travel) {
            if (!array_key_exists($travel['companyId'], $travelsCostByCompanyId)) {
                $travelsCostByCompanyId[$travel['companyId']] = 0;
            }
            $travelsCostByCompanyId[$travel['companyId']] += $travel['price'];
        }
        $companyTree = [];
        foreach ($companies as $company) {
            if (($company['parentId']) == '0') {
                $companyItem = new Company($company['id'], $company['name'], 0, []);
                $companyItem->setChildren(self::buildTree($companies, $travelsCostByCompanyId, $company['id']));
                if (is_countable($companyItem->getChildren())) {
                    $totalCost = $travelsCostByCompanyId[$company['id']] ?? 0;
                    /** @var Company $child */
                    foreach ($companyItem->getChildren() as $child) {
                        $totalCost += $child->getCost() ?? 0;
                    }
                    $companyItem->setCost($totalCost);
                }
                array_push($companyTree, $companyItem);
            }
        };
        print_r('<pre>');
        var_dump((array) $companyTree);
        print_r('<pre>');
        echo 'Total time: ' . (microtime(true) - $start);
    }

    public static function buildTree(
        array $companies,
        array $travelsCostByCompanyId,
        string $parentId = '0'
    )
    {
        $branch = [];
        foreach ($companies as $company) {
            if (($company['parentId']) == $parentId) {
                $companyItem = new Company(
                    $company['id'],
                    $company['name'],
                    $travelsCostByCompanyId[$company['id']] ?? 0,
                    []
                );
                $children = self::buildTree($companies, $travelsCostByCompanyId, $company['id']);
                if (isset($children) && is_countable($children)) {
                    $totalCost = $companyItem->getCost();
                    /** @var Company $child */
                    foreach ($children as $child) {
                        $totalCost += $child->getCost();
                    }
                    $companyItem->setCost($totalCost);
                }
                $companyItem->setChildren($children ?? []);
                $branch[] = $companyItem;
            }
        }

        return $branch;
    }
}

(new TestScript())->execute();
