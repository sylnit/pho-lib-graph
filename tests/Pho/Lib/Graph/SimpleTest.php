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

class SimpleTest extends \PHPUnit\Framework\TestCase 
{
    private $graph;

    public function setUp() {
        $this->graph = new Graph();
    }

    public function tearDown() {
        unset($this->graph);
    }

    public function testGraphAddGet() {
        $node = new Node($this->graph);
        $node_expected_to_be_identical = $this->graph->get($node->id());
        $this->assertEquals($node->id(), $node_expected_to_be_identical->id());
    }

    public function testGraphContains() {
        $node =new Node($this->graph);
        $this->assertTrue($this->graph->contains($node->id()));
    }

    public function testSubgraph() {
        $subgraph = new SubGraph($this->graph);
        $this->assertTrue($this->graph->contains($subgraph->id()));
    }

    public function testSubgraphRecursiveness() {
        $subgraph = new SubGraph($this->graph);
        $node = new Node($subgraph);
        $this->assertTrue($subgraph->contains($node->id()));
        $this->assertTrue($this->graph->contains($node->id()));
        $this->assertTrue($this->graph->contains($subgraph->id()));
    }

    public function testEdge() {
        $node1 = new Node($this->graph);
        $node2 = new Node($this->graph);
        $edge = new Edge($node1, $node2);
        $this->assertEquals($edge->id(), $node1->edges()->out()[0]->id());
        $this->assertEquals($edge->id(), $node2->edges()->in()[0]->id());
        $this->assertEquals($edge->id(), $node2->edges()->all()[0]->id());
    }

    public function testAttributes() {
        $faker = \Faker\Factory::create();
        $node1 = new Node($this->graph);
        $node2 = new Node($this->graph);
        $edge = new Edge($node1, $node2);
        $node1->attributes()->username = ($username1 = $faker->username);
        $node2->attributes()->username = ($username2 = $faker->username);
        $edge->attributes()->address = ($address = $faker->address);
        $this->assertEquals($username1, $node1->attributes()->username);
        $this->assertEquals($username2, $node2->attributes()->username);
        $this->assertEquals($address, $edge->attributes()->address);
    }

    public function testEdgeHeadTailIDs() {
        $faker = \Faker\Factory::create();
        $node1 = new Node($this->graph);
        $node2 = new Node($this->graph);
        $edge = new Edge($node1, $node2);
        $this->assertEquals($node1->id(), $edge->tailID());
        $this->assertEquals($node2->id(), $edge->headID());
    }

    public function testPredicateAssignment() {
        $new_predicate = new class extends Predicate { public function test() { return "works"; }};
        $node1 = new Node($this->graph);
        $node2 = new Node($this->graph);
        $edge1 = new Edge($node1, $node2);
        $this->assertFalse(method_exists($edge1->predicate(), "test"));
        $edge2 = new Edge($node1, $node2, $new_predicate);
        $this->assertEquals("works", $edge2->predicate()->test());
    }

    public function testID() {
        $id1 = ID::generate();
        $id2 = ID::fromString((string)$id1);
        $this->assertEquals($id1, $id2);
    }

    /**
     * @expectedException  \Pho\Lib\Graph\Exceptions\MalformedGraphIDException
     */
    public function testInvalidID() {
        ID::fromString("invalid");
    }

    public function testGraphToArray() {
        $node = new Node($this->graph);
        $this->assertEquals($node->id(), $this->graph->toArray()["members"][0]);
    }

    public function testSubGraphToArray() {
        $subgraph = new SubGraph($this->graph);
        $node = new Node($subgraph);
        // eval(\Psy\sh());
        $this->assertEquals($subgraph->id(), $this->graph->toArray()["members"][0]);
        $this->assertEquals($node->id(), $subgraph->toArray()["members"][0]);
    }

    public function testPredicateString() {
        $predicate = new Predicate();
        $this->assertEquals("predicate", (string) $predicate);
    }

    public function testChangeContext() 
    {
        $subgraph = new SubGraph($this->graph);
        $node = new Node($this->graph);
        $this->assertEquals($this->graph->id(), $node->context()->id());
        $node->changeContext($subgraph);
        $this->assertEquals($subgraph->id(), $node->context()->id());
    }

    public function testClusterLoadNodesFromIDArray()
    {
        $node1 =new Node($this->graph);
        $subgraph = new SubGraph($this->graph);
        $node2 = new Node($subgraph);
        $this->assertCount(1, $subgraph->members());
        $this->assertCount(3, $this->graph->members());

        $new_subgraph  = new SubGraph($this->graph);
        $new_subgraph->loadNodesFromIDArray($subgraph->toArray()["members"]);
        $this->assertEquals(1, $new_subgraph->count()); // count(), not members() because we don't want hydratedMembers yet.
        $this->assertEquals(4, $this->graph->count()); // only the new sub_graph, not its members because of overwrite.

    }
}