<?php
/*
 * Что касается SplObserver и SplSubject, то не существует реальных различий
между ними и Observer и ObservaЬle, за исключением, конечно, того, что нам больше
не нужно объявлять интерфейсы и мы должны изменить уточнение типа в параметрах
методов в соответствии с новыми именами. Но класс SplObjectStorage
предоставляет нам действительно полезный сервис. В первоначальном примере при
реализации метода Login: : detach () мы перебирали
в цикле все объекты типа ObservaЬle, сохраненные в массиве $observaЫe, чтобы
найти и удалить объект-аргумент. Класс SplObjectStorage скрытно выполняет всю
эту работу. В нем реализованы методы attach() и detach() , и его можно использовать
в цикле foreach для выполнения итераций по аналогии с массивом.
*/
class Login implements SplSubject {
    private $storage;
    const LOGIN_USER_UNKNOWN = 1;
    const LOGIN_WRONG_PASS   = 2;
    const LOGIN_ACCESS       = 3;

    function __construct() {
        $this->storage = new SplObjectStorage();
    }
    function attach( SplObserver $observer ) {
        $this->storage->attach( $observer );
    }

    function detach( SplObserver $observer ) {
        $this->storage->detach( $observer );
    }

    function notify() {
        foreach ( $this->storage as $obs ) {
            $obs->update( $this );
        }
    }

    function handleLogin( $user, $pass, $ip ) {
        switch ( rand(1,3) ) {
            case 1: 
                $this->setStatus( self::LOGIN_ACCESS, $user, $ip );
                $isvalid = true; break;
            case 2:
                $this->setStatus( self::LOGIN_WRONG_PASS, $user, $ip );
                $isvalid = false; break;
            case 3:
                $this->setStatus( self::LOGIN_USER_UNKNOWN, $user, $ip );
                $isvalid = false; break;
        }
        $this->notify();
        return $isvalid;
    }

    private function setStatus( $status, $user, $ip ) {
        $this->status = array( $status, $user, $ip ); 
    }

    function getStatus() {
        return $this->status;
    }

}

abstract class LoginObserver implements SplObserver {
    private $login;
    function __construct( Login $login ) {
        $this->login = $login; 
        $login->attach( $this );
    }

    function update( SplSubject $subject ) {
        if ( $subject === $this->login ) {
            $this->doUpdate( $subject );
        }
    }

    abstract function doUpdate( Login $login );
} 

class SecurityMonitor extends LoginObserver {
    function doUpdate( Login $login ) {
        $status = $login->getStatus(); 
        if ( $status[0] == Login::LOGIN_WRONG_PASS ) {
            // send mail to sysadmin 
            print __CLASS__.":\tsending mail to sysadmin\n"; 
        }
    }
}

class GeneralLogger  extends LoginObserver {
    function doUpdate( Login $login ) {
        $status = $login->getStatus(); 
        // add login data to log
        print __CLASS__.":\tadd login data to log\n"; 
    }
}

class PartnershipTool extends LoginObserver {
    function doUpdate( Login $login ) {
        $status = $login->getStatus(); 
        // check $ip address 
        // set cookie if it matches a list
        print __CLASS__.":\tset cookie if it matches a list\n"; 
    }
}

$login = new Login();
new SecurityMonitor( $login );
new GeneralLogger( $login );
$pt = new PartnershipTool( $login );
$login->detach( $pt );
for ( $x=0; $x<10; $x++ ) {
    $login->handleLogin( "bob","mypass", '158.152.55.35' );
    print "\n";
}

?>
