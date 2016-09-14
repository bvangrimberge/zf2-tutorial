<?php
/**
 * Created by PhpStorm.
 * User: bvg
 * Date: 13/09/2016
 * Time: 9:39
 */

namespace Album\Controller;

 use Album\Form\AlbumForm;
 use Album\Model\Album;
 use Zend\Debug\Debug;
 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;

 class AlbumController extends AbstractActionController
 {
     protected $albumTable;

     public function indexAction()
         {
             return new ViewModel(array(
                 'albums' => $this->getAlbumTable()->fetchAll(),
             ));
         }

     public function addAction()
     {
         $form = new AlbumForm();
         $form->get('submit')->setValue('Add');

          $request = $this->getRequest();
          if ($request->isPost()) {
              $album = new Album();
              $form->setInputFilter($album->getInputFilter());
              $form->setData($request->getPost());

              if ($form->isValid()) {
                  $album->exchangeArray($form->getData());
                  $this->getAlbumTable()->saveAlbum($album);

                  // Redirect to list of albums
                  return $this->redirect()->toRoute('album');
              }
          }
          return array('form' => $form);
     }

     public function editAction()
     {
         $id = (int) $this->params()->fromRoute('id', 0);
          if (!$id) {
              return $this->redirect()->toRoute('album', array(
                  'action' => 'add'
              ));
          }

          // Get the Album with the specified id.  An exception is thrown
          // if it cannot be found, in which case go to the index page.
          try {
              $album = $this->getAlbumTable()->getAlbum($id);
          }
          catch (\Exception $ex) {
              return $this->redirect()->toRoute('album', array(
                  'action' => 'index'
              ));
          }

          $form  = new AlbumForm();
          $form->bind($album);
          $form->get('submit')->setAttribute('value', 'Edit');

          $request = $this->getRequest();
          if ($request->isPost()) {
              $form->setInputFilter($album->getInputFilter());
              $form->setData($request->getPost());

              if ($form->isValid()) {
                  $this->getAlbumTable()->saveAlbum($album);

                  // Redirect to list of albums
                  return $this->redirect()->toRoute('album');
              }
          }

          return array(
              'id' => $id,
              'form' => $form,
          );
     }

     public function deleteAction()
     {
         $id = (int) $this->params()->fromRoute('id', 0);
         if (!$id) {
             return $this->redirect()->toRoute('album');
         }

         $request = $this->getRequest();
         if ($request->isPost()) {
             $this->getAlbumTable()->deleteAlbum($id);
         }

         $viewModel = $this->indexAction();
         $viewModel->setTemplate("album/index");
         return $viewModel;

     }

     public function getAlbumTable()
         {
             if (!$this->albumTable) {
                 $sm = $this->getServiceLocator();
                 $this->albumTable = $sm->get('Album\Model\AlbumTable');
             }
             return $this->albumTable;
         }
 }