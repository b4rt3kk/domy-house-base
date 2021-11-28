<?php

namespace Base\Services\Transport;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\TransportInterface;

/**
 * Klasa do wysyłki maili. W Factory należy ustawić nazwę modelu do zapisu wysłanych wiadomości oraz metodę transportu, np. sendmail
 * Można również określić domyślny adres nadawcy w przypadku gdy nie zostanie przekazany w wiadomości.
 * Konfiguracja odbywa się w Factory, dlatego by skorzystać z wysyłki nie trzeba podawać za każdym razem dodatkowych parametrów.
 * 
 * Przykładowe użycie:
 * $mail = $this->getServiceManager()->get(\Base\Services\Transport\Mail::class);
 * 
 * $message = new \Laminas\Mail\Message();
 * $message->setBody('This is the text of the email.');
 * $message->setFrom('test@example.com', "Sender's name");
 * $message->addTo('test@example.com', 'Recipient name');
 * $message->setSubject('TestSubject');
 * 
 * $mail->send($message);
*/
class Mail
{
    const SENT_MESSAGE_OK = 'Wiadomość została wysłana';
    
    /**
     * @var TransportInterface
     */
    protected $transport;
    
    protected $serviceManager;
    
    protected $mailSentModelName;
    
    protected $columnsMapping = [
        'id' => 'id',
        'subject' => 'subject',
        'body' => 'body',
        'is_sent' => 'is_sent',
        'sent_date' => 'sent_date',
        'email' => 'email',
        'sent_message' => 'sent_message',
        'is_error' => 'is_error',
        'created_at' => 'created_at',
        'created_by' => 'created_by',
    ];
    
    protected $defaultSender = [
        'email' => null,
        'name' => null,
    ];
    
    public function getTransport()
    {
        return $this->transport;
    }

    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }
    
    /**
     * @return \Laminas\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    public function getMailSentModelName()
    {
        return $this->mailSentModelName;
    }

    public function setMailSentModelName($mailSentModelName)
    {
        $this->mailSentModelName = $mailSentModelName;
    }
    
    public function getDefaultSender()
    {
        return $this->defaultSender;
    }

    public function setDefaultSender($email, $name = null)
    {
        $this->defaultSender = [
            'email' => $email,
            'name' => $name,
        ];
    }
    
    public function getColumnsMapping()
    {
        return $this->columnsMapping;
    }

    /**
     * Ustaw mapowanie kolumn dla modelu, klucze to nazwy rozpoznawane przez klasę, natomiast wartości to docelowa nazwa w bazie danych
     * @param array $columnsMapping
     */
    public function setColumnsMapping(array $columnsMapping)
    {
        $columns = array_keys($this->columnsMapping);
        
        foreach ($columnsMapping as $columnName => $mappedColumn) {
            if (array_key_exists($columnName, $columns)) {
                $this->columnsMapping[$columnName] = $mappedColumn;
            }
        }
    }
    
    public function send(Message $message, $rowData = [])
    {
        $transport = $this->getTransport();
        
        if (!$transport instanceof TransportInterface) {
            throw new \Exception(sprintf("Transport class has to implement %s", TransportInterface::class));
        }
        
        if (!$message->getFrom()->count()) {
            // jeśli brak danych nadawcy to uzupełnienie domyślnymi przekazanymi do klasy Mail
            $defaultSender = $this->getDefaultSender();
            $message->setFrom($defaultSender['email'], $defaultSender['name']);
        }
        
        $recipients = null;
        
        foreach ($message->getTo() as $to) {
            /* @var $to \Laminas\Mail\Address */
            $recipients .= $to->getEmail() . ';';
        }
        
        $id = $rowData[$this->getMappedColumnName('id')];
        
        if (empty($id)) {
            // nowy wiersz wiadomości
            $id = $this->createMailSentRow(array_merge([
                $this->getMappedColumnName('subject') => $message->getSubject(),
                $this->getMappedColumnName('body') => $message->getBodyText(),
                $this->getMappedColumnName('email') => rtrim($recipients, ';'),
            ], $rowData));
        }
        
        // wysyłka maila
        $transport->send($message);
        
        // update danych wiersza po wysyłce maila
        $this->updateMailSentRow($id, [
            $this->getMappedColumnName('is_sent') => '1',
            $this->getMappedColumnName('sent_date') => new \Laminas\Db\Sql\Expression("NOW()"),
            $this->getMappedColumnName('sent_message') => self::SENT_MESSAGE_OK,
        ]);
    }
    
    /**
     * Pobierz obiekt modelu
     * @return \Base\Db\Table\AbstractModel
     * @throws \Exception
     */
    protected function getModel()
    {
        $modelName = $this->getMailSentModelName();
        
        if (empty($modelName)) {
            throw new \Exception("Model class name for sent mails cannot be empty");
        }
        
        $model = $this->getServiceManager()->get($modelName);
        
        if (!$model instanceof \Base\Db\Table\AbstractModel) {
            throw new \Exception(sprintf("Model %s has to extends %s class", $modelName, \Base\Db\Table\AbstractModel::class));
        }
        
        return $model;
    }
    
    /**
     * Pobierz nazwę kolumny z modelu docelowego
     * @param string $column
     * @return string
     */
    protected function getMappedColumnName($column)
    {
        $mapping = $this->getColumnsMapping();
        
        return $mapping[$column];
    }
    
    /**
     * Pobierz wiersz maila do wysyłki/wysłanego na podstawie jego id
     * @param integer $id
     * @return \Base\Db\Table\AbstractEntity
     */
    protected function getMailSentRow($id)
    {
        $model = $this->getModel();
        
        $select = $model->select()
                ->where([$this->getMappedColumnName('id') => $id]);
        
        $row = $model->fetchRow($select);
        
        return $row;
    }
    
    protected function createMailSentRow($data)
    {
        $model = $this->getModel();
        
        $entity = $model->getEntity();
        $entity->exchangeArray($data);
        
        $id = $model->createRow($entity);
        
        return $id;
    }
    
    public function updateMailSentRow($id, $data)
    {
        $row = $this->getMailSentRow($id);
        
        if (empty($row)) {
            throw new \Exception(sprintf("Wiersz o id %s nie istnieje", $id));
        }
        
        $model = $this->getModel();
        
        $model->update($data, [$this->getMappedColumnName('id') => $id]);
    }
}
