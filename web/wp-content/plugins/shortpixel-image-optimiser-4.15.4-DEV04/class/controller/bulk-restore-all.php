<?php
namespace ShortPixel;

class BulkRestoreAll extends ShortPixelController
{
    protected static $slug = 'bulk-restore-all';
    protected $template = 'view-restore-all';

    protected $selected_folders = array();

    public function __construct()
    {
        parent::__construct();

    }

    public function randomCheck()
    {

      $output = '';
      for ($i=1; $i<= 10; $i++)
      {
          $output .= "<span><input type='radio' name='random_check[]' value='$i'  onchange='ShortPixel.checkRandomAnswer(event)' /> $i </span>";
      }

      return $output;
    }

    public function randomAnswer()
    {
      $correct = rand(1,10);
      $output = "<input type='hidden' name='random_answer' value='$correct'  data-target='#bulkRestore'  /> <span class='answer'>$correct</span> ";

      return $output;
    }

    public function getCustomFolders()
    {
      //wpshortPixel::refreshCustomFolders();
      $spMetaDao = $this->shortPixel->getSpMetaDao();
      $customFolders = $spMetaDao->getFolders();

      return $customFolders;

    }

    protected function processPostData($post)
    {
        if (isset($post['selected_folders']))
        {
            $folders = array_filter($post['selected_folders'], 'intval');
            //  var_dump($post['selected_folders']);
            if (count($folders) > 0)
            {
              $this->selected_folders = $folders;
            }
            unset($post['selected_folders']);
        }

        parent::processPostData($post);

    }

    public function setupBulk()
    {
      $this->checkPost(); // check if any POST vars are there ( which should be if custom restore is on )

      // handle the custom folders if there are any.
      if (count($this->selected_folders) > 0)
      {
          $spMetaDao = $this->shortPixel->getSpMetaDao();

          foreach($this->selected_folders as $folder_id)
          {
            $spMetaDao->setBulkRestore($folder_id);
          }
      }
    }


}
