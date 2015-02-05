<?php
namespace spacelife\models;

use spacelife\core\Model;
use spacelife\tools\Collection;

/**
* Users
*/
class UsersModel extends Model
{

    //  table
    protected $table = 'sl_user';

    //  entity
    protected $entityNS = 'spacelife\entity';
    protected $defaultEntity = 'UserEntity';

    //  validation rules
    protected $validation = [
        'id' => [
            'required' => 1,
            'filter'   => '^0-9'
        ],
        'user' => [
            'required' => 1,
            'filter'   => '^0-9'
        ],
        'login' => [
            'required' => [4, 255]
            , 'filter' => '^0-9a-zA-Z_.-'

        ],
        'password' => [
            'required' => 8,
        ],
        'password2' => [
            'required' => 8,
            'extra'    => 'passwordMatch'
        ],
        'current_password' => [
            'required' => 8,
            'extra'    => 'validCurrentPassword'
        ],
        'last_name' => [
            'required'     => [0, 255],
            'filter'       => '^0-9a-zA-Z_\'\sèéêàçñ-',
            'filter_clean' => true
        ],
        'first_name' => [
            'required'     => [0, 255],
            'filter'       => '^0-9a-zA-Z_\'\sèéêàçñ-',
            'filter_clean' => true
        ],
        'name_visible' => [
            'filter' => '^a-z'
        ],
        'gender' => [
            'filter' => '^a-z'
        ],
        'is_admin' => [
            'filter' => '^0-1'
        ],
        'email' => [
            'required'  => [6, 255]
            , 'filter'  => '^0-9a-zA-Z._@-'
            , 'pattern' => '^[0-9a-z_.-]+@[0-9a-z_.-]+$'
        ],
        'lang' => [
            'required' => [2, 2],
            'filter'   => '^0-9a-zA-Z-',
            'extra'    => 'validLanguage'
        ],
        'status' => [
            'filter' => '^0-9'
        ],
        'slhuman' => [
            'required' => [0, 0],
            'pattern'  => '^$'
        ],
        'token' => [
            'required' => [40, 40],
            'filter'   => '^0-9a-f'
        ],
        'last_login' => [
            'required' => [0, 0]
        ],
        'last_fail' => [
            'required' => [0, 0]
        ],
        'fail_count' => [
            'required' => [0, 0]
        ],
        'created' => [
            'required' => [0, 0]
        ],
        'updated' => [
            'required' => [0, 0]
        ],
        'facebook_id' => [
            'required' => [0, 64],
            'filter'   => '^0-9a-zA-Z'
        ]

    ];

    /**
    *   createNew
    *
    **/
    public function create($data)
    {
        $rules = ['login', 'email', 'password', 'slhuman', 'lang'];
        //  validating data
        if ($this->validate($data, $rules) === false) {
            return false;
        }

        if (!$this->loginAvailable($data->login)) {
            $this->validation_errors['login'] = 'extra';
            return false;
        }

        unset($data->slhuman);

        $data->password = password_hash($data->password, PASSWORD_DEFAULT);

        $id = $this->save($data);

        if ($id === false) {
            return false;
        } else {
            return $id;
        }

    }

    /**
    *   editProfile
    *
    **/
    public function editProfile($data)
    {
        unset($data->csrf);

        //  setting specific rules for profile values
        $rules = ['id', 'email', 'lang', 'gender', 'name_visible', 'first_name', 'last_name'];

        //  validating data against rules
        $data = $this->validate($data, $rules);

        //  if validation failed ... abort
        if ($data === false) {
            return false;
        }

        $current = $this->getById($data->id);

        if ($current->login != $data->login) {
            if (!$this->loginAvailable($data->login)) {
                $this->validation_errors['login'] = 'extra';
                return false;
            }
        }

        $data->updated = true;

        return $this->save($data);
    }


    /**
    *   getUser
    *
    **/
    public function getByLogin($data)
    {
        $fields = [
            '*',
            'unix_timestamp(last_fail) last_fail_ts'
        ];
        $filter = ['login' => $data->login];

        $user = $this->findFirst([
            'filter' => $filter,
            'fields' => $fields
            ]);

        return $user;
    }

    /**
    *   getByMail
    *
    **/
    public function getByMail($data)
    {
        $fields = [
            '*',
            'unix_timestamp(last_fail) last_fail_ts'
        ];
        $filter = ['email' => $data->email];

        return $this->findFirst(['filter' => $filter, 'fields' => $fields, 'entity' => true]);
    }


    /**
    *   getById
    *
    **/
    public function getById($user_id)
    {
        if (is_int($user_id)) {
            $id = $user_id;
        } else {
            $id = intval($user_id);
        }

        $filter = ['id' => $id];
        $fields = ['*'];

        return $this
                ->findFirst(['filter' => $filter, 'fields' => $fields, 'entity' => true]);
    }

    /**
    *   getProfile
    *
    **/
    public function getProfile($finder)
    {
        if (isset($finder['id'])) {
            $filter = ['id' => intval($finder['id'])];
        } elseif (isset($finder['login'])) {
            $filter = ['login' => $finder['login']];
        } else {
            return false;
        }

        $find = [
            'filter' => $filter,
            'fields' => ['*', 'unix_timestamp(created) created_ts']
        ];

        return $this->findFirst($find);
    }

    /**
    *   resetFailed
    *
    **/
    public function resetFailed($user_id)
    {
        $sql = 'update sl_user set fail_count=0 where id=:id';
        $this->db->execute($sql, [':id' => $user_id]);
    }

    /**
    *   setFailed
    *
    **/
    public function setFailed($user_id)
    {
        $user = [':id' => $user_id];

        $sql = 'update '.$this->table.' set fail_count=fail_count + 1, last_fail=CURRENT_TIMESTAMP where id=:id';
        $this->db->execute($sql, $user);

        if ($this->config->passwordRules->stickyLock) {
            $sql_failed = 'select fail_count from '.$this->table.' where id=:id';
            $failed = $this->db->selectOne($sql_failed, $user);

            $sql_lock = 'update '.$this->table.' set status=2 where id=:id';
            $this->db->execute($sql_lock, $user);
        }
    }

    /**
    *   setLastLogin
    *
    **/
    public function setLastLogin($user_id)
    {
        $sql = 'update '.$this->table.' set last_login=CURRENT_TIMESTAMP where id=:id';
        $this->db->execute($sql, [':id' => $user_id]);
    }


    /**
    *   updatePasswordHash
    *
    **/
    public function updatePasswordHash($password, $user_id)
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $sql = 'update '.$this->table.' set password=:password where id=:id';
        $this->db->execute($sql, [':password' => $password, ':id' => $user_id]);
    }


    /**
    *   unlock
    *
    **/
    public function unlock($user_id)
    {
        $sql = 'update '.$this->table.' set fail_count=0, status=1 where id=:id';
        return $this->db->execute($sql, [':id' => $user_id]);
    }

    /**
    *   changePassword
    *
    **/
    public function changePassword($data)
    {
        $rules = ['current_password', 'password', 'password2'];

        $data = $this->validate($data, $rules);

        if ($data === false) {
            return false;
        }

        //  values to inject in DB
        $values = [
            ':id' => $data->id,
            ':password' => password_hash($data->password, PASSWORD_DEFAULT)
        ];

        $sql_update = 'update '.$this->table.' set password=:password where id=:id';

        $change = $this->db->execute($sql_update, $values);

        return $change;
    }

    /**
    *   validCurrentPassword
    *
    **/
    protected function validCurrentPassword($data)
    {
        return password_verify($data->current_password, $data->current_password_db);
    }


    /**
    *   passwordMatch
    *
    **/
    protected function passwordMatch($data)
    {
        if ($data->password == $data->password2) {
            return true;
        }

        return false;
    }

    /**
    *   validLanguage
    *
    **/
    protected function validLanguage($data)
    {
        //  check provided language against langs available in config
        foreach ($this->config->langs as $lang) {
            if ($data->lang == $lang) {
                return true;
            }
        }
        return false;
    }

    /**
    *   storeResetToken
    *
    **/
    public function storeResetToken($data)
    {
        $sql = "insert into sl_reset_token set user=:user, token=:token, ts_valid=CURRENT_TIMESTAMP + interval :ts_valid second";
        return $this->query($sql, $data);
    }

    /**
    *   checkResetToken
    *
    **/
    public function checkResetToken($token)
    {
        //  delete expired tokens from table
        $sql_purge = "delete from sl_reset_token where ts_valid < unix_timestamp(now())";
        $this->query($sql_purge);

        $find = [
            'table' => 'sl_reset_token',
            'fields' => ['user'],
            'filter' => ['token' => $token]
        ];

        $data = $this->findFirst($find);

        if ($data) {
            return $this->getById($data->user);
        } else {
            return false;
        }
    }

    /**
    *   resetPassword
    *
    **/
    public function resetPassword($data)
    {
        $rules = ['id', 'password', 'password2'];

        $data = $this->validate($data, $rules);

        if ($data === false) {
            return false;
        }

        //  removing reset token from table
        $sql_delete = 'delete from sl_reset_token where user=:user';
        $this->query($sql_delete, [':user' => $data->id]);

        $values = [
            ':id' => $data->id,
            ':password' => password_hash($data->password, PASSWORD_DEFAULT)
        ];

        $sql_update = 'update '.$this->table.' set password=:password where id=:id';

        return $this->query($sql_update, $values);
    }

    /*
    *   admin_getList
    *       retrieves the list of users
    */
    public function admin_getList()
    {
        $sql = 'select * from '.$this->table.' where status >= 0';
        return $this->query($sql);
    }

    /**
    *   admin_editUser
    *
    **/
    public function admin_editUser($data)
    {
        $current = $this->getById($data->id);

        $rules = ['id', 'login', 'email', 'gender', 'first_name', 'last_name', 'name_visible', 'lang', 'is_admin'];

        $data = $this->validate($data, $rules);

        if ($data === false) {
            return false;
        }

        if ($current->login != $data->login) {
            if (!$this->loginAvailable($data->login)) {
                return false;
            }
        }

        unset($data->csrf);
        $data->updated = true;

        return $this->save($data);
    }

    /**
    *   admin_create
    *
    **/
    public function admin_create($data)
    {
        $rules = ['login', 'email', 'first_name', 'last_name', 'is_admin', 'gender'];

        $data = $this->validate($data, $rules);

        if ($data === false) {
            return false;
        }

        if (!$this->loginAvailable($data->login)) {
            return false;
        }

        unset($data->csrf);

        return $this->save($data);
    }

    /**
    *   storeActivation
    *
    **/
    public function storeActivation($id, $token)
    {
        $sql = "update sl_user set password='$token' where id=$id";
        return $this->query($sql);
    }

    /**
    *   getActivation
    *
    **/
    public function getActivation($login, $token)
    {
        $rules = ['login', 'token'];

        $data = ['login' => $login, 'token' => $token];

        $valid = $this->validate(new Collection($data), $rules);

        if ($valid === false) {
            return false;
        }

        $finder = [
            'filter' => [
                'login' => $login,
                'password' => $token
                ]
            ];

        return $this->findFirst($finder);

    }

    /**
    *   activate
    *
    **/
    public function activate($data, $user)
    {
        $rules = ['id', 'token', 'password', 'password2'];

        $data = $this->validate($data, $rules);

        if ($data === false) {
            return false;
        }

        //  check that user id matches id from form
        if ($data->id != $user->id) {
            return false;
        }

        // test that token from form matches db one
        if ($data->token != $user->password) {
            return false;
        }

        //  hash password
        $data->password = password_hash($data->password, PASSWORD_DEFAULT);
        //  add updated
        $data->updated = true;

        //  remove token
        unset($data->token);
        //  remove password confirmation
        unset($data->password2);
        //  remove csrf
        unset($data->csrf);

        //  save and return
        return $this->save($data);

    }

    /**
    *   validLogin
    *
    **/
    public function validLogin($login)
    {
        $rules = ['login'];
        $data = new \stdClass();
        $data->login = $login;
        $data = $this->validate($data, $rules);

        if ($data === false) {
            return false;
        }
        return $data->login;
    }

    /**
    *   loginAvailable
    *
    **/
    public function loginAvailable($login)
    {
        $login = $this->validLogin($login);
        if ($login === false) {
            return false;
        }

        $filter = ['filter' => ['login' => $login]];
        $test = $this->findFirst($filter);

        if ($test) {
            $this->validation_errors['login'] = 'unavailable';
            return false;
        }
        return true;
    }

    /**
     * [createFromFacebook description]
     * @param  [type] $user [description]
     * @return [type]       [description]
     */
    public function createFromFacebook($user)
    {
        //  genders list (needs to be improved)
        $genders = ['male' => 'mr', 'female' => 'mrs'];

        //  new object for the sl user
        $sl_user = new \stdClass();
        $sl_user->facebook_id = $user->getId();

        //  try to find a matching user un SL first
        $db_user = $this->getFacebookUser($sl_user);

        //  if no user found, create it
        if ($db_user === false) {
            //  get all data from facebook GraphUser object
            $sl_user->last_name = $user->getLastName();
            $sl_user->first_name = $user->getFirstName();
            $sl_user->email = $user->getEmail();
            $sl_user->login = preg_replace('/^([0-9a-z_.-]+)@[0-9a-z_.-]+$/', '\1', $sl_user->email);
            $fb_gender = $user->getGender();
            $sl_user->gender = isset($genders[$fb_gender]) ? $genders[$fb_gender] : 'oth';
            $fb_lang = $user->getProperty('locale');
            $sl_user->lang = in_array(substr($fb_lang, 0, 2), $this->config->langs) ? substr($fb_lang, 0, 2) : $this->config->default_lang;
            $sl_user->auth_type = 'facebook';

            //  validate data (never trust facebook !)
            $rules = ['facebook_id', 'last_name', 'first_name', 'email', 'login', 'lang', 'gender'];
            $data = $this->validate($sl_user, $rules);

            //  if data validation is OK, create it
            if ($data !== false) {
                $id = $this->save($data);
                $db_user = $this->getById($id);
                $db_user->first_login = true;
            } else {
                //  else throw a big bad nasty exception !
                throw new SLException("Error Creating Account", 1);
            }
        }

        //  finally return the SL user (found or created)
        return $db_user;

        // debug($sl_user);
    }

    /**
     * [getFacebookUser description]
     * @param  [type] $user [description]
     * @return [type]       [description]
     */
    public function getFacebookUser($user)
    {
        //  filter on facebook_id (which is not supposed to change ... ever !)
        $params = ['filter' => ['facebook_id' => $user->facebook_id]];
        $found = $this->findFirst($params);

        return $found;
    }

    /**
     * link a facebook account to an existing SLF account
     * @param  Facebook\GraphUser $fb_user GraphUser object from Facebook API
     * @param  integer $user_id SLF user ID
     * @return boolean success flag, false if failed
     */
    public function linkFacebookAccount($fb_user, $user_id)
    {
        $sl_user = $this->getById($user_id);

        if ($sl_user === false) {
            return false;
        }

        $data = new \stdClass();
        $data->id = $sl_user->id;
        $data->facebook_id = $fb_user->getId();

        $rules = ['id', 'facebook_id'];
        $data = $this->validate($data, $rules);

        if ($data === false) {
            return false;
        }

        $save = $this->save($data);
        if ($save === false) {
            return false;
        }

        return true;
    }


}
