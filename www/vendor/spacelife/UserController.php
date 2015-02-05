<?php
namespace spacelife;

use spacelife\core\Controller;
use spacelife\tools\Collection;
use spacelife\helper\Form;
use spacelife\core\Router;
use spacelife\tools\Facebook;

/**
* User
*/
class UserController extends Controller
{
    //  default template directory
    protected $template_dir = 'user';

    //  default models namespace
    protected $modelNS = 'spacelife\\models';

    //  default models to load
    protected $models = ['Users'];

    //  translations
    protected $translation_path = 'vendor/spacelife/translation';
    protected $translate = ['user'];
    protected $translate_admin = ['admin_user'];

    /*
    *   login
    *       login user
    */
    public function login($login = '')
    {
        $tpl = $this->template('login');
        $tpl->set('resetEnabled', $this->config->resetEnabled);
        if ($this->Users->validLogin($login) !== false) {
            $tpl->set('login', $login);
        } else {
            $tpl->set('login', false);
        }

        $data = [];
        if ($this->request->data) {
            if ($this->processLogin()) {
                $this->redirect('home/index');
            } else {
                $tpl->set('message', $this->T->user->login->failure);
                $data = $this->request->data;
            }
        }

        $tpl->set('form', $this->createForm($data, [], 'user'));

        $this->setContent($tpl->render());
    }

    /**
     * Logs a user in using Facebook Connect
     * @return Redirect redirects tu user to the relevant page (Facebook or internal login)
     */
    public function loginFacebook()
    {
        //  if facebook login is not enabled ... redirect to regular login form
        if ($this->config->facebook->enable === false) {
            $this->redirect('user/login');
        }

        $fbconnect = new Facebook($this->session, $this->config->facebook);
        $user = $fbconnect->connect(trim($this->config->siteUrl, '/').Router::url('user/loginFacebook'));

        if (is_string($user)) {
            $this->redirect($user, -1);
        } else {
            $this->openFacebookSession($user);
        }
    }

    /**
     * openFacebookSession opens a session on SLF using Facebook Connect
     * @param  Facebook\GraphUser $user GraphUser object (Facebook profile)
     * @return redirect       Redirects the user to the SLF home page
     */
    protected function openFacebookSession($user)
    {
        // debug($user);
        $user_slf = $this->Users->createFromFacebook($user);
        $this->session->login($user_slf);
        $this->Users->setLastLogin($this->session->user_id);
        if ($this->session->first_login == 1) {
            $this->session->setFlash(['type' => 'success', 'message' => $this->T->user->facebook->created]);
        } else {
            $this->session->setFlash(['type' => 'success', 'message' => $this->T->user->facebook->login]);
        }
        $this->redirect('home/index');
    }

    public function linkFacebook()
    {
        $fbconnect = new Facebook($this->session, $this->config->facebook);
        $user = $fbconnect->connect(trim($this->config->siteUrl, '/').Router::url('user/linkFacebook'));

        if (is_string($user)) {
            $this->redirect($user, -1);
        } else {
            $link = $this->Users->linkFacebookAccount($user, $this->session->user_id);
            if ($link) {
                $this->session->setFlash(['type' => 'success', 'message' => $this->T->user->facebook->link->success]);
            } else {
                $this->session->setFlash(['type' => 'danger', 'message' => $this->T->user->facebook->link->failure]);
            }
            $this->redirect('user/profile');
        }
    }


    /*
    *   processLogin
    *       login the user
    */
    protected function processLogin()
    {
        //  retrieve user from DB
        if (strpos($this->request->data->login, '@')) {
            $this->request->data->email = $this->request->data->login;
            $user = $this->Users->getByMail($this->request->data);
        } else {
            $user = $this->Users->getByLogin($this->request->data);
        }

        //  now
        $now = date("U");

        //  if user was found
        if ($user) {

            //  user locked ?
            if ($user->status == 2) {
                return false;
            }

            //  lockdown
            if ($user->fail_count >= $this->config->passwordRules->maxRetry) {
                if (($user->last_fail_ts + $this->config->passwordRules->lockdown) <= $now && $this->config->stickyLock === false) {
                    //  remove temp lock to allow real password check
                    $this->Users->resetFailed($user->id);
                } else {
                    //  temp lock
                    return false;
                }
            }

            //  check password
            if (password_verify($this->request->data->password, $user->password)) {
                //  remove temp lockdown
                $this->Users->resetFailed($user->id);

                //  create session
                $this->session->login($user);

                //  password needs rehash (future proof ?)
                if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
                    $this->Users->updatePasswordHash($this->request->data->password, $user->id);
                }

                //  update user last_login
                $this->Users->setLastlogin($user->id);

                return true;
            } else {
                $this->Users->setFailed($user->id);
                return false;
            }
        } else {
            return false;
        }

        //  if we arrived here ... there is a problem
        $this->error($this->T->user->login->failure);
    }

    /**
    *   logout
    *
    **/
    public function logout()
    {
        $this->session->logout();
        $this->redirect('home/index');
    }

    /**
    *   profile
    *
    **/
    public function profile($login = '')
    {
        $login = preg_replace('/[^a-zA-Z0-9_]/', '', $login);
        //  if login is set: disp another profile
        if ($login != '' && $login != $this->session->login) {
            return $this->profileOther($login);
        }

        $user = $this->Users->getProfile(['id' => $this->session->user_id]);

        if ($user === false) {
            $this->e404($this->T->user->profile->notfound);
        }

        $tpl = $this->template('profile');
        $tpl->set('user', $user);
        $tpl->set('profile', $this->T->user->profile);
        $tpl->set('dateFormat', $this->T->user->dateFormat);

        $this->setContent($tpl->render());
    }

    /**
    *   api_profile
    *
    **/
    public function api_profile($login = '')
    {
        $this->setApiContent(['toto', 'plop']);
    }

    /**
    *   profileOther
    *
    **/
    protected function profileOther($login)
    {
        $user = $this->Users->getProfile(['login' => $login]);

        if ($user === false) {
            $this->e404($this->T->user->profile->notfound);
        }

        $tpl = $this->template('profile_other');
        $tpl->set('user', $user);
        $tpl->set('profile', $this->T->user->profile);
        $tpl->set('dateFormat', $this->T->user->dateFormat);

        $this->setContent($tpl->render());
    }

    /**
    *   editProfile
    *
    **/
    public function editProfile()
    {
        $user = $this->Users->getProfile(['id' => $this->session->user_id]);

        if ($user === false) {
            $this->e404($this->T->user->profile->notfound);
        }

        $data = $user;
        $errors = [];

        if ($this->request->data) {
            $this->session->checkCsrf();

            //  check id
            if (isset($this->request->data->id) && $this->request->data->id != $this->session->user_id) {
                return false;
            }

            $edit = $this->Users->editProfile($this->request->data);

            if ($edit) {
                $this->session->setFlash(['message' => $this->T->user->editprofile->success, 'type' => 'success']);
                $this->redirect('user/profile');
            } else {
                $this->session->setFlash(['message' => $this->T->user->editprofile->failure, 'type' => 'error']);
                $data = $this->request->data;
                $errors = $this->Users->validation_errors;
            }
        }


        $tpl = $this->template('edit_profile');
        $tpl->set('login', $user->login);
        $tpl->set('form', $this->createForm($data, $errors, 'user'));
        $this->setContent($tpl->render());
    }

    /**
    *   editProfileProcess
    *
    **/
    protected function editProfileProcess()
    {
        //  check csrf to prevent unwanted changes

        if ($edit === false) {
            return false;
        }

        return true;
    }


    /**
    *   register
    *
    **/
    public function register()
    {
        //  load template
        $tpl = $this->template('register');

        //  setting errors to false
        $errors = [];
        $data = ['lang' => $this->session->lang];


        if ($this->request->data) {
            $data = $this->request->data;
            $registration = $this->registerProcess();
            if ($registration) {
                return $this->registred();
            } else {
                $errors = $this->Users->validation_errors;
                $tpl->set('message_failure', $this->T->user->register->failure);
            }

        }

        $tpl->set('form', $this->createForm($data, $errors, 'user'));

        $this->setContent($tpl->render());
    }

    /**
    *   registerProcess
    *
    **/
    protected function registerProcess()
    {
        $create = $this->Users->create($this->request->data);

        if ($create === false) {
            return false;
        }

        return true;
    }

    /**
    *   registred
    *
    **/
    protected function registred()
    {
        $tpl = $this->template('registred');
        $tpl->set('user', $this->request->data);

        $this->setContent($tpl->render());
    }


    /*
    *   lost
    *       password lost
    */
    public function lost()
    {
        if ($this->config->resetEnabled === false) {
            $this->redirect('home/index');
        }

        if ($this->request->data) {
            return $this->passwordLost();
        }

        $tpl = $this->template('lost');

        $tpl->set('form', $this->createForm([], [], 'user'));

        $this->setContent($tpl->render());
    }

    /*
    *   passwordLost
    *       send an email
    */
    protected function passwordLost()
    {
        $user = $this->Users->getByMail($this->request->data);

        if ($user !== false) {

            //  setting user lang
            $this->setLang($user->lang);
            $this->loadTranslation('user', true);

            //  gen lost token (and store it somewhere)
            $lost_token = sha1($user->id.microtime(true).$user->login);
            $token_valid = $this->config->resetLinkValidity;

            if (!$this->Users->storeResetToken([':token' => $lost_token, ':ts_valid' => $token_valid, ':user' => $user->id])) {
                $this->error('Could not initialize reset token');
            }

            $tpl = $this->template('lost_mail');
            $tpl->set('user', $user);
            $tpl->set('lost_token', $lost_token);
            $tpl->set('siteUrl', $this->config->siteUrl);
            $mail = new \stdClass();
            $mail->body = $tpl->render();
            $mail->title = $this->T->user->lostPassword->mail->title;
            $mail->recipient = [$user->email];

            //  create model for mails

            $this->loadModel('Mails', 'spacelife\core\Mails');
            $this->Mails->send($mail);

            //  notify
        }

        $this->session->setFlash(['type' => 'success', 'message' => $this->T->user->lostPassword->mail->sent]);
        $this->redirect('user/login');
    }

    /**
    *   resetPassword
    *
    **/
    public function resetPassword($token)
    {
        //  get user from token
        $user = $this->Users->checkResetToken($token);

        //  check token validity
        if ($user === false) {
            $this->redirect('home/index');
        }

        $errors = [];
        $data = ['id' => $user->id, 'token' => $token];

        //  if form was submitted
        if ($this->request->data) {
            if ($this->resetPasswordProcess($user, $token)) {
                $this->session->setFlash(['type' => 'success', 'message' => $this->T->user->resetPassword->success]);
                $this->redirect('user/login');
            } else {
                $errors = $this->Users->validation_errors;
                $this->session->setFlash(['type' => 'danger', 'message' => $this->T->user->resetPassword->failure]);
            }
        }

        $tpl = $this->template('reset_from_token');
        $tpl->set('form', $this->createForm($data, $errors, 'user'));
        $tpl->set('login', $user->login);
        $tpl->set('token', $token);


        $this->set('content', $tpl->render());
    }

    /**
    *   resetPasswordProcess
    *
    **/
    protected function resetPasswordProcess($user, $token)
    {
        //  checking token
        if ($token != preg_replace('/[^a-f0-9]/', '', $this->request->data->token)) {
            return false;
        }

        //  checking user id
        if ($user->id != preg_replace('/[^0-9]/', '', $this->request->data->id)) {
            return false;
        }

        //  if we came that far, everything should be OK
        return $this->Users->resetPassword($this->request->data);
    }

    /**
    *   changePassword
    *
    **/
    public function changePassword()
    {
        $tpl = $this->template('change_password');

        $data = [];
        $errors = [];

        if ($this->request->data) {

            $this->session->checkCsrf();

            $data = $this->request->data;
            $data->id = $this->session->user_id;
            $user = $this->Users->getById($data->id);
            $data->current_password_db = $user->password;


            if ($this->Users->changePassword($data) !== false) {
                $this->session->setFlash(['message' => $this->T->user->changePassword->success, 'type' => 'success']);
                $this->redirect('user/profile');
            } else {
                $errors = $this->Users->validation_errors;

                $tpl->set('message_failure', $this->T->user->changePassword->failure);
                $this->session->setFlash(['message' => $this->T->user->changePassword->failure, 'type' => 'danger']);
            }
        }

        $tpl->set('form', $this->createForm($data, $errors, 'user'));

        $tpl->set('login', $this->session->login);

        $this->setContent($tpl->render());
    }


    /**
    *   activate
    *
    **/
    public function activate($login, $token)
    {
        $user = $this->Users->getActivation($login, $token);

        if ($user === false) {
            $this->e404('404');
        }

        $data = $user;
        $errors = [];
        if ($this->request->data) {
            $this->session->checkCsrf();
            $activation = $this->Users->activate($this->request->data, $user);
            if ($activation) {
                $this->session->setFlash(['message' => $this->T->user->activate->notices->success, 'type' => 'success']);
                $this->redirect('user/login/'.$user->login);
            } else {
                $this->session->setFlash(['message' => $this->T->user->activate->notices->failure, 'type' => 'error']);
            }
            $errors = $this->Users->validation_errors;
        }

        $tpl = $this->template('activate');
        $tpl->set('user', $user);
        $tpl->set('form', $this->createForm($data, $errors, 'user'));

        $this->setContent($tpl->render());
    }


    /**
    *   ADMIN Methods
    *
    */

    /*
    *   admin_index
    *       alias for admin_list
    */
    public function admin_index()
    {
        return $this->admin_list();
    }

    /*
    *   admin_list
    *       lists all registred users
    */
    public function admin_list()
    {
        $users = $this->Users->admin_getList();

        $tpl = $this->template('admin_list');
        $tpl->set('users', $users);
        $tpl->set('form', $this->createForm([], [], 'admin_user'));

        $this->setContent($tpl->render());
    }

    /*
    *   admin_view
    *       view details of a user
    */
    public function admin_view($id)
    {
        $user = $this->Users->getById($id);

        $tpl = $this->template('admin_view');
        $tpl->set('user', $user);

        $this->setContent($tpl->render());
    }

    /*
    *   admin_edit
    *       admin edition of a user
    */
    public function admin_edit($id)
    {
        $user = $this->Users->getById($id);

        $errors = [];
        if ($this->request->data) {
            $this->session->checkCsrf();

            $saved = $this->Users->admin_editUser($this->request->data);

            if ($saved) {
                $this->session->setFlash(['message' => $this->T->admin_user->edit->success, 'type' => 'success']);
                $this->redirect('admin/user/view/'.$user->id);
            } else {
                $errors = $this->Users->validation_errors;
                $this->session->setFlash(['message' => $this->T->admin_user->edit->failure, 'type' => 'error']);
            }
        }

        $tpl = $this->template('admin_edit');
        $tpl->set('user', $user);
        $tpl->set('errors', $errors);
        $tpl->set('csrf', $this->session->csrf());
        $tpl->set('form', $this->createForm($user, $errors, 'admin_user'));

        $this->setContent($tpl->render());
    }

    /*
    *   admin_delete
    *       deletes a user
    */
    public function admin_delete($id)
    {
        $user = $this->Users->getById($id);

        if ($this->request->data) {
            $this->session->checkCsrf();
            if ($this->Users->delete(['id' => $id])) {
                $this->session->setFlash(['message' => $this->T->admin_user->delete->success, 'type' => 'success']);
                $this->redirect('admin/user/list');
            } else {
                $this->session->setFlash(['message' => $this->T->admin_user->delete->failure, 'type' => 'error']);
            }
        }

        $tpl = $this->template('admin_delete');
        $tpl->set('user', $user);
        $tpl->set('csrf', $this->session->csrf());
        $this->setContent($tpl->render());
    }

    /**
    *   admin_unlock
    *
    **/
    public function admin_unlock($id)
    {
        $user = $this->Users->getById($id);

        if ($user) {
            $unlocked = $this->Users->unlock($id);
            if ($unlocked) {
                $this->session->setFlash(['message' => $this->T->admin_user->unlock->success, 'type' => 'success']);
                $this->redirect('admin/user/view/'.$id);
            }
        }

        $this->session->setFlash(['message' => $this->T->admin_user->unlock->failure, 'type' => 'error']);
        $this->redirect('admin/user/view/'.$id);
    }

    /**
    *   admin_create
    *
    **/
    public function admin_create()
    {
        $data = [];
        $errors = [];
        if ($this->request->data) {
            $this->session->checkCsrf();
            $user_id = $this->Users->admin_create($this->request->data);
            $errors  =$this->Users->validation_errors;
            if ($user_id) {
                $user = $this->Users->getById($user_id);
                $activation_token = sha1(microtime(true).$user->login.$this->session->csrf());

                //  store token in password field
                $this->Users->storeActivation($user->id, $activation_token);

                $this->session->setFlash(['message' => $this->T->admin_user->create->success, 'type' => 'success']);

                $this->loadModel('Mails', 'spacelife\core\Mails');

                $tplmail = $this->template('admin_mail_create', $user->lang);
                $tplmail->set('user', $user);
                $tplmail->set('activation_token', $activation_token);
                $mail = new \stdClass();
                $mail->body = $tplmail->render();
                $mail->title = $this->T->admin_user->create->mail_title;
                $mail->recipient = [$user->email];
                $mail_sent = $this->Mails->send($mail);

                if ($mail_sent) {
                    $this->session->setFlash(['message' => $this->T->admin_user->create->mail_sent, 'type' => 'success']);
                } else {
                    $this->session->setFlash(['message' => $this->T->admin_user->create->mail_failed, 'type' => 'warning']);
                }

                $this->redirect('admin/user/list');
            }
            $this->session->setFlash(['message' => $this->T->admin_user->create->failure, 'type' => 'error']);
            $data = $this->request->data;
        }

        $tpl = $this->template('admin_create');
        $tpl->set('csrf', $this->session->csrf());
        $tpl->set('form', $this->createForm($data, $errors, 'admin_user'));

        $this->setContent($tpl->render());
    }

}

