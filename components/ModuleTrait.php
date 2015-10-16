<?php

namespace achertovsky\components;

use Yii;

/*
 * Trait for modules in the system
 */
trait ModuleTrait
{
    /**
     * @var \yii\swiftmailer\Mailer Mailer instance
     */
    private $_mail;

    public function init()
    {
        if ($this->controllerNamespace === null) {
            $class = get_class($this);
            if (($pos = strrpos($class, '\\')) !== false) {
                $this->controllerNamespace = substr($class, 0, $pos) . '\\controllers\\' . Yii::$app->id;
            }
        }

        //Set path to views for module application
        $this->setViewPath($this->getBasePath() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . Yii::$app->id);

        parent::init();
    }

    public function getModel($name)
    {
        $modelClass = '';
        $class = get_class($this);
        if (($pos = strrpos($class, '\\')) !== false) {
            $modelClass = substr($class, 0, $pos) . '\\models\\' . ucfirst($name);
        }
        return $modelClass ? new $modelClass : null;
    }

    /**
     * @return \yii\swiftmailer\Mailer Mailer instance with predefined templates.
     */
    public function getMail()
    {
        if ($this->_mail === null) {
            $this->_mail = Yii::$app->getMailer();
            $this->_mail->htmlLayout = '@modules/' . $this->id . '/mails/layouts/html';
            $this->_mail->textLayout = '@modules/' . $this->id . '/mails/layouts/text';
            $this->_mail->viewPath = '@modules/' . $this->id . '/mails/views';
        }

        return $this->_mail;
    }
}

