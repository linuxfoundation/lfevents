<?php
use org\bovigo\vfs\vfsStream;


class CustomMediaTest extends WP_UnitTestCase
{
  public static function wpSetUpBeforeClass( $factory ) {
        $settings = new WPShortPixelSettings();
        $settings->backupImages = 1;


  }

  public function setUp()
  {

  }

  public function tearDown()
  {

  }

  public function testCustomBackup()
  {
    $post = $this->factory->post->create_and_get();
    $attachment_id = $this->factory->attachment->create_upload_object( __DIR__ . '/assets/test-image.jpg', $post->ID );

  }
}
