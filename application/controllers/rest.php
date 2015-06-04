<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('application/libraries/REST_Controller.php');

class Rest extends REST_Controller {
	// This class is the controller for the RESTful interface to the CTC
    // database.

    // To handle CORS (Cross Origin Resource Sharing) it first issues
    // the access-control headers, and then quits if it's an OPTIONS request,
    // which is the "pre-flight" browser generated request to check access.
    // See http://stackoverflow.com/questions/15602099/http-options-error-in-phil-sturgeons-codeigniter-restserver-and-backbone-js

    public function __construct()
    {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, HEAD, DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }
        
        parent::__construct();
        $this->load->database('ctcweb9_joom1');
        $this->load->model('tripreportmodel');
    }
    
    
    protected function log($type, $message) {
        // Call log_message with the same parameters, but prefix the message
        // by *rest* for easy identification.
        log_message($type, '*rest* ' . $message);
    }
    
    
    protected function error($message, $httpCode=400) {
        // Generate the http response containing the given message with the given
        // HTTP response code. Log the error first.
        $this->log('error', $message);
        $this->response($message, $httpCode);
    }
    
    
    public function index_get() {
        $this->response('Please access this API via the tripreports collection');
    }
    
    // ****************************
    //        TRIP REPORTS
    // ****************************

    // Put (i.e. update) a trip report
    public function tripreports_put($report_id) {
        global $userData;
        if ($userData['userid'] == 0) {
            $this->response("Not logged in", 401);
        }
        $data = $this->put(null, True); // All input data, xss filtered
        $this->tripreportmodel->saveReport($data, FALSE);
    }
    
    // Post a new trip report
    public function tripreports_post() {
        global $userData;
        if ($userData['userid'] == 0) {
            $this->response("Not logged in", 401);
        }
        $data = $this->post(null, True); // All input data, xss filtered
        $this->tripreportmodel->saveReport($data, TRUE);
    }
    
    public function tripreportyears_get() {
        // A list of all years for which trip reports exist in desc. order
        $years = $this->tripreportmodel->getAllYears();
        $this->response($years);
    }
 
    // Get a trip rerport
    public function tripreports_get($id=NULL) {
        if ($id) {
            $row = $this->tripreportmodel->getById($id);
        } else {
            $row = $this->tripreportmodel->create();
        }
        $this->response($row);
    }
    
    // Get a list of trip reports for a given year.
    public function yearstripreports_get($year) {
        $rows = $this->tripreportmodel->getByYear($year);
        $this->response($rows);
    }
    
    // Delete the given trip 
    public function tripreports_delete($id) {
        global $userData;
        if ($userData['userid'] == 0) {
            $this->response("Not logged in", 401);
        }
        
    }
    
    // ********************************
    //       IMAGES
    // ********************************   
    
    
    public function tripimages_post() {
        // Add a new trip image to the database. Body is a JSON record with the
        // following attributes:
        //    name: the image name (usually the original filename)
        //    caption: the caption to be displayed (if desired)
        //    dataUrl: the image in the form of a dataUrl
        global $userData;
        if ($userData['userid'] == 0) {
            $this->response("Not logged in", 401);
        }
        $this->load->model('imagemodel');
        $name = $this->post('name', false);
        $caption = $this->post('caption', false);
        $dataUrl = $this->post('dataUrl', false);
        $this->log('debug', "Received image $name, captioned $caption");
        $id = $this->imagemodel->create_from_dataurl($name, $caption, $dataUrl);
        $this->response(array('id'=>$id));
    }
    
    
    public function tripimages_get($image_id) {
        // Get the specified image. Returns a JSON record containing the
        // following attributes:
        //    name: the image name (usually the original filename)
        //    caption: the caption to be displayed (if desired)
        //    width:  the width in pixels
        //    height: the height in pixels
        //    url:  an url that can be used in an <img> tag to display the image
        //    t_width: the width in pixels of the thumbnail image
        //    t_height: the height in pixels of the thumbnail
        //    t_url: an url that can be used to display the image
    }
    
    public function tripimages_delete($image_id) {
        // Delete a specified trip image
        global $userData;
        if ($userData['userid'] == 0) {
            $this->response("Not logged in", 401);
        }
        $this->load->model('imagemodel');
        $this->imagemodel->delete($image_id);
        
    }
    
    // ********************************
    //       GPXS
    // ********************************  
    
    public function gpxs_post() {
        global $userData;
        if ($userData['userid'] == 0) {
            $this->response("Not logged in", 401);
        }
        
    }
    
    public function gpxs_get($gpx_id) {
        
    }
    
    public function gpxs_delete($gpx_id) {
        global $userData;
        if ($userData['userid'] == 0) {
            $this->response("Not logged in", 401);
        }
        
    }
}