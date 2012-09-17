<?php

require_once __DIR__ . '/../../../../PhraseanetWebTestCaseAuthenticatedAbstract.class.inc';

use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DescriptionTest extends \PhraseanetWebTestCaseAuthenticatedAbstract
{
    protected $client;

    /**
     * Default route test
     */
    public function testRouteDescription()
    {
        $appbox = self::$application['phraseanet.appbox'];
        $databox = array_shift($appbox->get_databoxes());
        $name = "testtest" . uniqid();
        $field = \databox_field::create(self::$application, $databox, $name, false);
        $id = $field->get_id();
        $this->client->request("POST", "/admin/description/" . $databox->get_sbas_id() . "/", array(
            'field_ids' => array($id)
            , 'name_' . $id       => $name
            , 'multi_' . $id      => 1
            , 'indexable_' . $id  => 1
            , 'src_' . $id        => '/rdf:RDF/rdf:Description/IPTC:SupplementalCategories'
            , 'required_' . $id   => 0
            , 'readonly_' . $id   => 0
            , 'type_' . $id       => 'string'
            , 'vocabulary_' . $id => 'User'
        ));

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $field->delete();
    }

    public function testPostDelete()
    {
        $appbox = self::$application['phraseanet.appbox'];
        $databox = array_shift($appbox->get_databoxes());
        $name = "test" . uniqid();
        $field = \databox_field::create(self::$application, $databox, $name, false);
        $id = $field->get_id();

        $this->client->request("POST", "/admin/description/" . $databox->get_sbas_id() . "/", array(
            'todelete_ids' => array($id)
        ));

        $this->assertTrue($this->client->getResponse()->isRedirect());

        try {
            $field = \databox_field::get_instance(self::$application, $databox, $id);
            $field->delete();
            $this->fail("should raise an exception");
        } catch (\Exception $e) {

        }
    }

    public function testPostCreate()
    {
        $appbox = self::$application['phraseanet.appbox'];
        $databox = array_shift($appbox->get_databoxes());

        $name = 'test' . uniqid();

        $this->client->request("POST", "/admin/description/" . $databox->get_sbas_id() . "/", array(
            'newfield' => $name
        ));

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $fields = $databox->get_meta_structure();
        $find = false;

        foreach ($fields as $field) {
            if ($field->get_name() === databox_field::generateName($name)) {
                $field->delete();
                $find = true;
            }
        }

        if ( ! $find) {
            $this->fail("should have create a new field");
        }
    }

    public function testPostDescriptionException()
    {
        $appbox = self::$application['phraseanet.appbox'];
        $databox = array_shift($appbox->get_databoxes());

        $this->client->request("POST", "/admin/description/" . $databox->get_sbas_id() . "/", array(
            'todelete_ids' => array('unknow_id')
        ));

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $name = "test" . uniqid();
        $field = \databox_field::create(self::$application, $databox, $name, false);
        $id = $field->get_id();
        $this->client->request("POST", "/admin/description/" . $databox->get_sbas_id() . "/", array(
            'field_ids' => array($id)
            , 'name_' . $id       => $name
            , 'multi_' . $id      => 1
            , 'indexable_' . $id  => 1
            , 'src_' . $id        => '/rdf:RDF/rdf:Description/IPTC:SupplementalCategories'
            , 'required_' . $id   => 0
            , 'readonly_' . $id   => 0
            , 'type_' . $id       => 'string'
            , 'vocabulary_' . $id => 'Unknow_Vocabulary'
        ));

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $field->delete();

        $name = "test" . uniqid();
        $field = \databox_field::create(self::$application, $databox, $name, false);
        $id = $field->get_id();
        $this->client->request("POST", "/admin/description/" . $databox->get_sbas_id() . "/", array(
            'field_ids' => array($id)
            , 'multi_' . $id      => 1
            , 'indexable_' . $id  => 1
            , 'src_' . $id        => '/rdf:RDF/rdf:Description/IPTC:SupplementalCategories'
            , 'required_' . $id   => 0
            , 'readonly_' . $id   => 0
            , 'type_' . $id       => 'string'
            , 'vocabulary_' . $id => 'Unknow_Vocabulary'
        ));

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $field->delete();

        $name = "test" . uniqid();
        $field = \databox_field::create(self::$application, $databox, $name, false);
        $field->set_indexable(false);
        $field->set_required(true);
        $field->set_readonly(true);
        $id = $field->get_id();
        $this->client->request("POST", "/admin/description/" . $databox->get_sbas_id() . "/", array(
            'field_ids' => array($id)
            , 'name_' . $id       => $name
            , 'multi_' . $id      => 1
            , 'indexable_' . $id  => 1
            , 'src_' . $id        => 'unknow_Source'
            , 'required_' . $id   => 0
            , 'readonly_' . $id   => 0
            , 'type_' . $id       => 'string'
            , 'vocabulary_' . $id => 'Unknow_Vocabulary'
        ));

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->assertTrue($field->is_readonly());
        $this->assertTrue($field->is_required());
        $this->assertFalse($field->is_multi());
        $this->assertFalse($field->is_indexable());
        $field->delete();


        $name = "test" . uniqid();
        $field = \databox_field::create(self::$application, $databox, $name, false);
        $id = $field->get_id();
        $this->client->request("POST", "/admin/description/" . $databox->get_sbas_id() . "/", array(
            'field_ids' => array('unknow_id')
            , 'name_' . $id       => $name
            , 'multi_' . $id      => 1
            , 'indexable_' . $id  => 1
            , 'src_' . $id        => '/rdf:RDF/rdf:Description/IPTC:SupplementalCategories'
            , 'required_' . $id   => 0
            , 'readonly_' . $id   => 0
            , 'type_' . $id       => 'string'
            , 'vocabulary_' . $id => 'Unknow_Vocabulary'
        ));

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $field->delete();
    }

    public function testPostDescriptionRights()
    {
        $this->setAdmin(false);

        $databox = array_shift(self::$application['phraseanet.appbox']->get_databoxes());
        $name = "test" . uniqid();
        $field = \databox_field::create(self::$application, $databox, $name, false);
        $id = $field->get_id();

        try {
            $this->client->request("POST", "/admin/description/" . $databox->get_sbas_id() . "/", array(
                'field_ids' => array($id)
                , 'name_' . $id       => $name
                , 'multi_' . $id      => 1
                , 'indexable_' . $id  => 1
                , 'src_' . $id        => '/rdf:RDF/rdf:Description/IPTC:SupplementalCategories'
                , 'required_' . $id   => 0
                , 'readonly_' . $id   => 0
                , 'type_' . $id       => 'string'
                , 'vocabulary_' . $id => 'User'
            ));
            print($this->client->getResponse()->getContent());
            $this->fail('Should throw an AccessDeniedException');
        } catch (AccessDeniedHttpException $e) {

        }

        $field->delete();
    }

    public function testGetDescriptionException()
    {
        $this->setAdmin(false);
        $appbox = self::$application['phraseanet.appbox'];

        $databox = array_shift($appbox->get_databoxes());

        try {
            $this->client->request("GET", "/admin/description/" . $databox->get_sbas_id() . "/");
            $this->fail('Should throw an AccessDeniedException');
        } catch (AccessDeniedHttpException $e) {

        }
    }

    public function testGetDescription()
    {
        $appbox = self::$application['phraseanet.appbox'];
        $databox = array_shift($appbox->get_databoxes());

        $this->client->request("GET", "/admin/description/" . $databox->get_sbas_id() . "/");
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testGetMetadatas()
    {
        $appbox = self::$application['phraseanet.appbox'];
        $databox = array_shift($appbox->get_databoxes());

        $this->client->request("GET", "/admin/description/metadatas/search/", array('term' => ''));
        $this->assertTrue($this->client->getResponse()->isOk());

        $datas = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(array(), $datas);

        $this->client->request("GET", "/admin/description/metadatas/search/", array('term' => 'xmp'));
        $this->assertTrue($this->client->getResponse()->isOk());

        $datas = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue(is_array($datas));
        $this->assertGreaterThan(0, count($datas));
    }
}
