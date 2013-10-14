<?php
    class Sample extends WufooTransformer
    {   
        const SECRET_KEY = 'ENTER YOUR KEY HERE';

        public function authenticate($handshake_key,&$normal_post)
        {   
            return ($handshake_key == self::SECRET_KEY);
        }
        
        public function run()
        {
            $this->transformPost();
            die(print_r($_POST));
        }
    }
