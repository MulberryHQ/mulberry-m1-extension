<?php

class Mulberry_Warranty_Adminhtml_System_Config_ExportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var string
     */
    private $mediaUrl;

    /**
     * @var array
     */
    private $categoryUrlCache = [];

    protected function _construct()
    {
        parent::_construct();
        $this->mediaUrl = rtrim(Mage::getBaseDir('media'), '/');
    }

    public function generateAction()
    {
        $page = $this->getRequest()->getParam('page');
        $pageData = $this->exportProductsByPage($page);
        $this->getResponse()->setBody(
            json_encode(['success' => true, 'content' => $pageData['content'], 'lastPage' => $pageData['lastPage']])
        );
    }

    public function exportProductsByPage($page)
    {
        $result = [
            'content' => [],
            'lastPage' => '',
        ];
        try {
            $count = 0;
            $productCollection = $this->getProductCollection($page);
            $result['lastPage'] = $productCollection->getLastPageNumber();
            foreach ($productCollection->getItems() as $product) {
                $count++;
                $result['content'][] = $this->fetchProductData($product);
            }

            $json = serialize($result['content']) . '|';
            $this->writeToFile(
                $json,
                'crawlerDataPaginated.json',
                $page == 1
            );
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Error while crawling products: %s', $e->getMessage()));
        }

        return $result;
    }

    /**
     * @param int $page
     * @return object
     */
    private function getProductCollection($page = 1)
    {
        $productCollection = $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCollection->addAttributeToSelect('*');
        $productCollection->setPageSize(100);
        $productCollection->setCurPage($page);

        return $productCollection;
    }

    /**
     * @param Product $product
     * @return array
     */
    private function fetchProductData($product)
    {
        $result = [];

        $result['title'] = $product->getName();
        $result['text'] = $product->getDescription();
        $result['price'] = $product->getPrice();
        $result['url'] = preg_replace('/\?.*/', '', $product->getUrlInStore());
        $result['images'] = $this->fetchProductImages($product);
        $result['categories'] = $this->fetchProductCategoryData($product);

        return $result;
    }

    /**
     * @param $product
     * @return array
     */
    private function fetchProductImages($product)
    {
        $result = [];
        $productMediaEntities = $product->getMediaGalleryImages();
        if ($productMediaEntities) {
            foreach ($productMediaEntities as $mediaEntity) {
                $result[] = sprintf("%s%s", $this->mediaUrl, $mediaEntity->getFile());
            }
        }
        return $result;
    }

    /**
     * @param Product $product
     * @return array
     */
    private function fetchProductCategoryData($product)
    {
        $result = [];
        if ($product->getCategoryIds()) {
            foreach ($product->getCategoryIds() as $categoryId) {
                if (!isset($this->categoryUrlCache[$categoryId])) {
                    $category = Mage::getModel('catalog/category')->load($categoryId);
                    $this->categoryUrlCache[$categoryId]['name'] = $category->getName();
                    $this->categoryUrlCache[$categoryId]['url'] = $category->getUrl();
                }

                $result[] = $this->categoryUrlCache[$categoryId];
            }
        }

        return $result;
    }

    /**
     * @param $content
     * @param $fileName
     * @param false $deleteOldFile
     */
    private function writeToFile($content, $fileName, $deleteOldFile = false)
    {
        try {
            $varFolder = Mage::getBaseDir('var');
            $filePath = sprintf('%s%s%s', $varFolder, DIRECTORY_SEPARATOR, $fileName);
            if (file_exists($filePath) && $deleteOldFile) {
                unlink($filePath);
            }

            $file = fopen($filePath, 'a');
            fwrite($file, $content);
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Could not write to file: %s', $e->getMessage()));
        }
    }

    public function downloadAction()
    {
        $fileContent = $this->readDataFromFile();
        $parsedJson = $this->parseContent($fileContent);
        $this->getResponse()->setBody(json_encode(['success' => (bool) $parsedJson, 'content' => $parsedJson]));
    }

    /**
     * @return string
     */
    private function readDataFromFile()
    {
        try {
            $varFolder = Mage::getBaseDir('var');
            $filePath = sprintf('%s%s%s', $varFolder, DIRECTORY_SEPARATOR, 'crawlerDataPaginated.json');
            if (file_exists($filePath)) {
                return file_get_contents($filePath);
            }
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Could not read file: %s', $e->getMessage()));
        }

        return '';
    }

    /**
     * @param string $fileContent
     * @return string
     */
    private function parseContent($fileContent)
    {
        $result = [];
        $jsonChunks = explode('|', $fileContent);

        foreach ($jsonChunks as $chunk) {
            if ($chunk) {
                $array = unserialize($chunk);
                $result = array_merge($result, $array);
            }
        }

        return serialize($result);
    }

    /**
     * Check is allowed access to action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config');
    }
}
