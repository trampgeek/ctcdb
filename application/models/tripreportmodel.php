<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

// This class provides an interface to the trip reports in the database.
// Intended for use by the rest API.


class Tripreportmodel extends CI_Model {
    public $id = 0;
    public $trip_type = 'club';
    public $year = 0;
    public $month = 0;
    public $day = 0;
    public $duration = 0;  // In days
    public $date_display = '';
    public $user_set_date_display = 0;  // Boolean true iff $date_display set explicitly
    public $title = '';
    public $body = '';
    public $map_copyright = '';
    public $uploader_id = 0;
    public $uploader_name = '';
    public $upload_date = '';
    public $deleter_id = 0;
    // The last 3 attributes are arrays of gpx, image and map database rows
    // for this trip report, with the addition of ordering and the removal
    // of any blob fields.
    public $gpxs;
    public $images;
    public $maps;
    
    
    public function create() {
        // Just returns a new empty trip report
        $this->year = strftime('%Y');
        $this->upload_date = strftime('%Y%m%d');
        $this->gpxs = array();
        $this->images = array();
        $this->maps = array();
        return $this;
    }
    
    public function getById($tripId) {
        // Initialise self from the row of the tripreports table corresponding
        // to the given trip report id, enhanced by lists of gpx, image and
        // map ids.
        $q = $this->db->get_where('jos_tripreport', 
            array('id' => $tripId, 'deleter_id' => NULL));
        if ($q->num_rows != 1) {
            throw new Exception("Tripreportmodel:getById($tripId) failed");
        }
        foreach ($q->row_array() as $key=>$value) {
            $this->$key = $value;
        }
        
        $this->loadEntities($tripId, 'image');
        $this->loadEntities($tripId, 'gpx');
        $this->loadEntities($tripId, 'map');
        return $this;
    }
    
    public function getAllYears() {
        // Return a list of all the years for which trip reports exist,
        // in descending order.
        $this->db->select('year');
        $this->db->distinct();
        $this->db->order_by('year desc');
        $q = $this->db->get('jos_tripreport');
        $years = array();
        foreach($q->result() as $row) {
            $years[] = $row->year;
        }
        return $years;
    }
    
    public function getByYear($year) {
        // Return a list of all the trip reports for the given year.
        // Each is an object with a trip report ID, a date and a title
        $this->db->order_by('month desc, day desc');
        $q = $this->db->get_where('jos_tripreport',
            array('year'=>$year, 'deleter_id' => NULL));
        return $q->result();
    }
    
    private function loadEntities($tripId, $entity) {
        // Load the list of image, gpx or map rowss respectively for the
        // given tripId into $this, where $entity
        // is 'image', 'gpx' or 'map' respectively.
        // A hack is that there is no separate map table as maps are images,
        // so we do 'map' as a special case.
        // The entity list that gets plugged into $this is a list of 
        // objects, one per matching row of the jos_$entity table but without
        // any blob fields. Also, the ordering field from the bridging table
        // is added, too.
        $mainTable = $entity === 'map' ? 'jos_image' : 'jos_' . $entity;
        $fieldData = $this->db->field_data($mainTable);
        $fields = array();
        foreach ($fieldData as $field) {
            if (strpos(strtolower($field->type), 'blob') === FALSE) {
                $fields[] = "$mainTable.{$field->name}";
            }
        }
        $this->db->select(implode(',', $fields) . ',ordering');
        $this->db->from($mainTable);
        $entityId = $entity . '_id';
        $this->db->join("jos_tripreport_$entity", "$mainTable.id = jos_tripreport_$entity.$entityId");
        $this->db->where(array("jos_tripreport_$entity.tripreport_id" => $tripId));
        $this->db->order_by('ordering');
        $entities = $this->db->get();
        $listFieldName = $entity . 's';
        $this->$listFieldName = array();

        foreach ($entities->result() as $row) {
            array_push($this->$listFieldName, $row);
        }
    }
}
