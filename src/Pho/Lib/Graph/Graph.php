<?php

/*
 * This file is part of the Pho package.
 *
 * (c) Emre Sokullu <emre@phonetworks.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pho\Lib\Graph;

/**
 * Graph contains nodes
 * 
 * Graph contains objects that implement NodeInterface
 * interface, such as Node and Subgraph objects, but not
 * Edges.
 * 
 * @author Emre Sokullu <emre@phonetworks.org>
 */
class Graph implements GraphInterface, \SplObserver, \Serializable
{

    use SerializableTrait;
    use ClusterTrait;

    /**
     * {@inheritdoc}
     */
    public function id(): ID
    {
        return ID::root();
    }

    public function update(\SplSubject $node): void
    {
        $this->observeNodeDeletion($node);
    }
    
}