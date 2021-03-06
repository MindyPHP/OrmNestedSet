<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Orm;

/**
 * Class TreeManager.
 *
 * @method TreeModel|null get($conditions = [])
 * @method array|TreeModel[] all()
 *
 * @property TreeModel $model
 */
class TreeManager extends Manager
{
    /**
     * @return TreeQuerySet
     */
    public function getQuerySet()
    {
        if (null === $this->qs) {
            $model = $this->getModel();

            $this->qs = new TreeQuerySet([
                'model' => $model,
                'modelClass' => get_class($model),
                'connection' => $model->getConnection(),
            ]);
            $this->qs->order(['lft']);
        }

        return $this->qs;
    }

    /**
     * Named scope. Gets descendants for node.
     *
     * @param bool $includeSelf
     * @param int  $depth       the depth
     *
     * @return $this
     */
    public function descendants($includeSelf = false, $depth = null)
    {
        $this
            ->getQuerySet()
            ->descendants($includeSelf, $depth);

        return $this;
    }

    /**
     * Named scope. Gets children for node (direct descendants only).
     *
     * @param bool $includeSelf
     *
     * @return $this
     */
    public function children($includeSelf = false)
    {
        $this
            ->getQuerySet()
            ->children($includeSelf);

        return $this;
    }

    /**
     * Named scope. Gets ancestors for node.
     *
     * @param bool $includeSelf
     * @param int  $depth       the depth
     *
     * @return $this
     */
    public function ancestors($includeSelf = false, $depth = null)
    {
        $this
            ->getQuerySet()
            ->ancestors($includeSelf, $depth);

        return $this;
    }

    /**
     * @param bool $includeSelf
     *
     * @return $this
     */
    public function parents($includeSelf = false)
    {
        $this
            ->getQuerySet()
            ->parents($includeSelf);

        return $this;
    }

    /**
     * Named scope. Gets root node(s).
     *
     * @return $this
     */
    public function roots()
    {
        $this
            ->getQuerySet()
            ->roots();

        return $this;
    }

    /**
     * Named scope. Gets parent of node.
     *
     * @return $this
     */
    public function parent()
    {
        $this
            ->getQuerySet()
            ->parent();

        return $this;
    }

    /**
     * Named scope. Gets previous sibling of node.
     *
     * @return $this
     */
    public function prev()
    {
        $this
            ->getQuerySet()
            ->prev();

        return $this;
    }

    /**
     * Named scope. Gets next sibling of node.
     *
     * @return $this
     */
    public function next()
    {
        $this
            ->getQuerySet()
            ->next();

        return $this;
    }

    /**
     * @param string $key
     *
     * @return \Mindy\Orm\TreeManager
     */
    public function asTree($key = 'items')
    {
        $this
            ->getQuerySet()
            ->asTree($key);

        return $this;
    }

    /**
     * Completely rebuild broken tree
     */
    public function rebuild()
    {
        $i = 0;
        $skip = [];
        while (0 != $this->filter(['lft__isnull' => true])->count()) {
            ++$i;
            $fixed = 0;
            echo 'Iteration: '.$i.PHP_EOL;

            $clone = clone $this;
            /** @var TreeModel[] $models */
            $models = $clone
                ->exclude(['pk__in' => $skip])
                ->filter(['lft__isnull' => true])
                ->order(['parent_id'])
                ->all();

            foreach ($models as $model) {
                $model->lft = $model->rgt = $model->level = $model->root = null;
                if ($model->saveRebuild()) {
                    $skip[] = $model->pk;
                    ++$fixed;
                }
                echo '.';
            }
            echo PHP_EOL;
            echo 'Fixed: '.$fixed.PHP_EOL;
        }
    }
}
