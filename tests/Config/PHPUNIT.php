<?php
use BLW\Interfaces\Object as ObjectInterface;

class PHPUnit_ObjectTest extends \PHPUnit_Framework_TestCase
{
    const TEST_CLASS = '\\Object';

    const CHILD1 = 0;
    const CHILD2 = 1;
    const CHILD3 = 2;
    const CHILD4 = 3;

    const GRANDCHILD1 = 0;
    const GRANDCHILD2 = 1;
    const GRANDCHILD3 = 0;
    const GRANDCHILD4 = 1;
    const GRANDCHILD5 = 0;
    const GRANDCHILD6 = 1;

    protected static $Parent      = NULL;
    protected static $Child1      = NULL;
    protected static $Child2      = NULL;
    protected static $Child3      = NULL;
    protected static $Child4      = NULL;
    protected static $GrandChild1 = NULL;
    protected static $GrandChild2 = NULL;
    protected static $GrandChild3 = NULL;
    protected static $GrandChild4 = NULL;
    protected static $GrandChild5 = NULL;
    protected static $GrandChild6 = NULL;

    public function _Initialize()
    {
        $TEST_CLASS = static::TEST_CLASS;
        $TEST_CLASS::Initialize(array('foo'=>1, 'hard_init'=>true));

        // Data tests
        $this->assertArrayHasKey('foo', $TEST_CLASS::$DefaultOptions);
        $this->assertArrayNotHasKey('hard_init', $TEST_CLASS::$DefaultOptions);

        // Options tests
        if (isset($TEST_CLASS::$DefaultOptions['foo'])) {
            $this->assertEquals(1, $TEST_CLASS::$DefaultOptions['foo']);
        }
    }

    /**
     * @depends _Initialize
     */
    public function _GetInstance()
    {
        $TEST_CLASS = static::TEST_CLASS;

        // Create Parent
        static::$Parent = $TEST_CLASS::GetInstance(array('ID'=>'Parent', 'bar'=>1));

        $this->assertEquals('Parent', static::$Parent->GetID());
        $this->assertEquals(1, @static::$Parent->Options->foo);
        $this->assertEquals(1, @static::$Parent->Options->bar);
        $this->assertNull(static::$Parent->parent());

        if(!static::$Parent instanceof \BLW\Interfaces\Singleton) {
            $Duplicate = $TEST_CLASS::GetInstance(static::$Parent);

            $this->assertNotSame(static::$Parent, $Duplicate);
            $this->assertEquals(static::$Parent->GetID(), $Duplicate->GetID());
            $this->assertEquals(static::$Parent->GetOptions(), $Duplicate->GetOptions());
        }

        // Create Children
        static::$Child1 = $TEST_CLASS::GetInstance(array('ID'=>'Child1', 'Parent'=>static::$Parent, 'bar'=>1));
        static::$Child2 = $TEST_CLASS::GetInstance(array('ID'=>'Child2', 'Parent'=>static::$Parent, 'bar'=>1));
        static::$Child3 = $TEST_CLASS::GetInstance(array('ID'=>'Child3', 'Parent'=>static::$Parent, 'bar'=>1));
        static::$Child4 = $TEST_CLASS::GetInstance(array('ID'=>'Child4', 'Parent'=>static::$Parent, 'bar'=>1));

        // Create GrandChildren
        static::$GrandChild1 = $TEST_CLASS::GetInstance(array('ID'=>'GrandChild1', 'bar'=>1));
        static::$GrandChild2 = $TEST_CLASS::GetInstance(array('ID'=>'GrandChild2', 'bar'=>1));
        static::$GrandChild3 = $TEST_CLASS::GetInstance(array('ID'=>'GrandChild3', 'bar'=>1));
        static::$GrandChild4 = $TEST_CLASS::GetInstance(array('ID'=>'GrandChild4', 'bar'=>1));
        static::$GrandChild5 = $TEST_CLASS::GetInstance(array('ID'=>'GrandChild5', 'bar'=>1));
        static::$GrandChild6 = $TEST_CLASS::GetInstance(array('ID'=>'GrandChild6', 'bar'=>1));
    }

	/**
	 * @depends _GetInstance
	 * @expectedException InvalidArgumentException
	 */
	public function _GetInstanceException1()
	{
        $TEST_CLASS = static::TEST_CLASS;
	    new $TEST_CLASS('foo');
	}

	/**
	 * @depends _GetInstance
	 * @expectedException InvalidArgumentException
	 */
	public function _GetInstanceException2()
	{
        $TEST_CLASS = static::TEST_CLASS;
	    new $TEST_CLASS(array('ID' => '   '));
	}

	/**
	 * @depends _GetInstance
	 * @expectedException InvalidArgumentException
	 */
	public function _GetInstanceException3()
	{
        $TEST_CLASS = static::TEST_CLASS;
	    new $TEST_CLASS(new \stdClass);
	}

	/**
	 * @depends _GetInstance
	 */
    public function _serialize()
    {
        static::$Parent->foo = 1;
        static::$Parent->foo = 1;
        static::$Child1->foo = 1;
        static::$Child2->foo = 1;
        static::$Child3->foo = 1;
        static::$Child4->foo = 1;

        $Serialized = unserialize(serialize(static::$Parent));

        $this->assertSame(static::$Parent->foo, $Serialized->foo);
        $this->assertEquals(static::$Parent, $Serialized);
    }

	/**
	 * @depends _serialize
	 */
	public function _Save()
	{
		static::$Parent->Save(sys_get_temp_dir() . '/temp-' .date('l') . '.php');

		$this->assertTrue(file_exists(sys_get_temp_dir() . '/temp-' .date('l') . '.php'));
	}

	/**
	 * @depends _Save
	 */
	public function _Load()
	{
		$Saved = include(sys_get_temp_dir() . '/temp-' .date('l') . '.php');

		@unlink(sys_get_temp_dir() . '/temp-' .date('l') . '.php');

		$this->assertEquals(static::$Parent, $Saved);
		$this->assertFalse(file_exists(sys_get_temp_dir() . '/temp-' .date('l') . '.php'));
	}

	/**
	 * @depends _GetInstance
	 */
	public function _on()
	{
        $TEST_CLASS   = static::TEST_CLASS;
	    $Called       = '';
 	    $Test         = function() use (&$Called) {
 	        $debug    = debug_backtrace();
 	        $Called   = '';

 	        foreach ($debug as $trace) if (strstr($trace['function'], 'do')) {
 	            if($trace['function'] == 'doDispatch') continue;

                if(strpos($trace['function'], '_') === false) {
                    $Called = $trace['function'];
                    break;
                }
 	        }
        };

	    $TEST_CLASS::onCreate($Test);

	    $Object = $TEST_CLASS::GetInstance()
	       ->onSetID($Test)
	       ->onSerialize($Test)
	    ;

	    if(static::$Parent instanceof \BLW\Interfaces\Singleton) {
	        $Object->onUpdate($Test);
 	    }

	    elseif(!static::$Parent instanceof \BLW\Interfaces\Singleton) {
    	    $this->assertEquals('doCreate', $Called);
	    }

	    $Object->SetID('foo');
	    $this->assertEquals('doSetID', $Called);

	    if(static::$Parent instanceof \BLW\Interfaces\Singleton) {
	        $Object->SetInstance($TEST_CLASS::GetInstance());
	        $this->assertEquals('doUpdate', $Called);
	    }

	    serialize($Object);
	    $this->assertEquals('doSerialize', $Called);
	}

	/**
	 * @depends _on
	 * @expectedException \BLW\Model\InvalidClassException
	 */
	public function _onException()
	{
	    static::$Parent->onSetID(array($this, 'froooglidoo'));
	}

	/**
	 * @depends _GetInstance
	 * @expectedException \BLW\Model\InvalidClassException
	 */
	public function _SingletonException()
	{
        $TEST_CLASS = static::TEST_CLASS;
	    new $TEST_CLASS();
	}

	public function _Shut_Down()
	{
	    static::$Parent      = NULL;
	    static::$Child1      = NULL;
	    static::$Child2      = NULL;
	    static::$Child3      = NULL;
	    static::$Child4      = NULL;
	    static::$GrandChild1 = NULL;
	    static::$GrandChild2 = NULL;
	    static::$GrandChild3 = NULL;
	    static::$GrandChild4 = NULL;
	    static::$GrandChild5 = NULL;
	    static::$GrandChild6 = NULL;
 	}
}

class PHPUnit_IteratorTest extends \PHPUnit_ObjectTest
{
	/**
	 * @depends _GetInstance
	 */
	public function _on()
	{
        $TEST_CLASS   = static::TEST_CLASS;
	    $Called       = '';
 	    $Test         = function() use (&$Called) {
 	        $debug    = debug_backtrace();
 	        $Called   = '';

 	        foreach ($debug as $trace) if (strstr($trace['function'], 'do')) {
 	            if($trace['function'] == 'doDispatch') continue;

 	            if(strpos($trace['function'], '_') === false) {
                    $Called = $trace['function'];
                    break;
                }
 	        }
        };

	    $TEST_CLASS::onCreate($Test);

	    $Object = $TEST_CLASS::GetInstance()
	       ->onSetID($Test)
	       ->onAdd($Test)
	       ->onUpdate($Test)
	       ->onDelete($Test)
	       ->onSerialize($Test)
	    ;

	    $this->assertEquals('doCreate', $Called);

	    $Object->SetID('foo');
	    $this->assertEquals('doSetID', $Called);

	    $Object->push($TEST_CLASS::GetInstance());
	    $this->assertEquals('doAdd', $Called);

	    $Object[0] = $TEST_CLASS::GetInstance();
	    $this->assertEquals('doUpdate', $Called);

	    unset($Object[0]);
	    $this->assertEquals('doDelete', $Called);

	    serialize($Object);
	    $this->assertEquals('doSerialize', $Called);
	}

    /**
     * @depends _GetInstance
     */
    public function _push()
    {
        // Map all objects
        static::$Parent->push(static::$Child1);
        static::$Parent->push(static::$Child2);
        static::$Parent->push(static::$Child3);
        static::$Parent->push(static::$Child4);

        if(static::$Parent instanceof \BLW\Interfaces\Element) {
            $this->assertEquals(5, count(static::$Parent));
        }

        else {
            $this->assertEquals(4, count(static::$Parent));
        }

        static::$Parent[static::CHILD1]->push(static::$GrandChild1);
        static::$Parent[static::CHILD1]->push(static::$GrandChild2);
        static::$Parent[static::CHILD2]->push(static::$GrandChild3);
        static::$Parent[static::CHILD2]->push(static::$GrandChild4);
        static::$Parent[static::CHILD3]->push(static::$GrandChild5);
        static::$Parent[static::CHILD3]->push(static::$GrandChild6);
        static::$Parent[static::CHILD4]->push(static::$GrandChild1);
        static::$Parent[static::CHILD4]->push(static::$GrandChild2);

        $self = $this;

        // Assert Bar Property
        static::$Parent->walk(function(\BLW\Interfaces\Event $Event) use(&$self) {
            if ($Event->GetSubject() instanceof ObjectInterface) {
                $self->assertEquals(1, @$Event->GetSubject()->Options->foo);
                $self->assertEquals(1, @$Event->GetSubject()->Options->bar);
            }
        });

        // Assert ID's
        $this->assertEquals('Child1', static::$Parent[static::CHILD1]->GetID());
        $this->assertEquals('Child2', static::$Parent[static::CHILD2]->GetID());
        $this->assertEquals('Child3', static::$Parent[static::CHILD3]->GetID());
        $this->assertEquals('Child4', static::$Parent[static::CHILD4]->GetID());
        $this->assertEquals('GrandChild1', static::$Parent[static::CHILD1][static::GRANDCHILD1]->GetID());
        $this->assertEquals('GrandChild2', static::$Parent[static::CHILD1][static::GRANDCHILD2]->GetID());
        $this->assertEquals('GrandChild3', static::$Parent[static::CHILD2][static::GRANDCHILD3]->GetID());
        $this->assertEquals('GrandChild4', static::$Parent[static::CHILD2][static::GRANDCHILD4]->GetID());
        $this->assertEquals('GrandChild5', static::$Parent[static::CHILD3][static::GRANDCHILD5]->GetID());
        $this->assertEquals('GrandChild6', static::$Parent[static::CHILD3][static::GRANDCHILD6]->GetID());
        $this->assertEquals('GrandChild1', static::$Parent[static::CHILD4][static::GRANDCHILD1]->GetID());
        $this->assertEquals('GrandChild2', static::$Parent[static::CHILD4][static::GRANDCHILD2]->GetID());

        // Assert Lineage
        foreach (static::$Parent as $Child) if ($Child instanceof ElementInterface) {
            $this->assertSame(static::$Parent, $Child->GetParent());

            if($Child->GetID() == 'Child4') continue;

            foreach ($Child as $GrandChild) if ($GrandChild instanceof ElementInterface) {
                $this->assertSame($Child, $GrandChild->GetParent());
            }
        }
    }

	/**
	 * @depends _push
	 */
	public function _seek()
	{
	    static::$Parent->seek(static::CHILD2);
	    $this->assertSame(static::$Child2, static::$Parent->current());

	    static::$Parent->seek(static::CHILD4);
        $this->assertSame(static::$Parent->current(), static::$Parent[static::CHILD4]);
	}
}