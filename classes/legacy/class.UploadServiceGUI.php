<?php

declare(strict_types=1);
/**
 * Disclaimer: This file is part of the GeoGebra Repository Object plugin for ILIAS.
 */


use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\ResourceStorage\Services as ResourceStorage;
use ILIAS\FileUpload\Handler\AbstractCtrlAwareUploadHandler;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\Handler\BasicFileInfoResult;

/**
 * Class UploadServiceGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 *
 * @ilCtrl_isCalledBy UploadServiceGUI : ilGeoGebraPluginGUI
 */
class UploadServiceGUI extends AbstractCtrlAwareUploadHandler
{
    private ResourceStorage $storage;
    private StorageStakeHolder $stakeholder;

    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->storage = $DIC['resource_storage'];
        $this->stakeholder = new StorageStakeHolder();
    }

    /**
     * @throws IllegalStateException
     * @throws Exception
     */
    protected function getUploadResult(): HandlerResult
    {
        $this->upload->process();
        /**
         * @var $result UploadResult
         */
        $array = $this->upload->getResults();
        $result = end($array);
        if ($result instanceof UploadResult && $result->isOK()) {
            $i = $this->storage->manage()->upload($result, $this->stakeholder);
            $status = HandlerResult::STATUS_OK;
            $identifier = $i->serialize();
            $message = 'Upload ok';
        } else {
            $status = HandlerResult::STATUS_FAILED;
            $identifier = '';
            $message = $result->getStatus()->getMessage();
        }

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }

    protected function getRemoveResult(string $identifier): HandlerResult
    {
        $id = $this->storage->manage()->find($identifier);
        if ($id !== null) {
            $this->storage->manage()->remove($id, $this->stakeholder);

            return new BasicHandlerResult($this->getFileIdentifierParameterName(), HandlerResult::STATUS_OK, $identifier, 'file deleted');
        } else {
            return new BasicHandlerResult($this->getFileIdentifierParameterName(), HandlerResult::STATUS_FAILED, $identifier, 'file not found');
        }
    }

    /** @noinspection DuplicatedCode */
    public function getInfoResult(string $identifier): ?FileInfoResult
    {
        $id = $this->storage->manage()->find($identifier);
        if ($id === null) {
            return new BasicFileInfoResult($this->getFileIdentifierParameterName(), 'unknown', 'unknown', 0, 'unknown');
        }
        $r = $this->storage->manage()->getCurrentRevision($id)->getInformation();

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(),
            $identifier,
            $r->getTitle(),
            $r->getSize(),
            $r->getMimeType()
        );
    }

    public function getInfoForExistingFiles(array $file_ids): array
    {
        $infos = [];
        foreach ($file_ids as $file_id) {
            $id = $this->storage->manage()->find($file_id);
            if ($id === null) {
                continue;
            }
            $r = $this->storage->manage()->getCurrentRevision($id)->getInformation();

            $infos[] = new BasicFileInfoResult($this->getFileIdentifierParameterName(), $file_id, $r->getTitle(), $r->getSize(), $r->getMimeType());
        }

        return $infos;
    }
}
