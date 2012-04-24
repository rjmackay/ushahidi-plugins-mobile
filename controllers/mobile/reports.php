<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Mobile Controller
 * Generates KML with PlaceMarkers and Category Styles
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @module	   Mobile Controller	
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
* 
*/

class Reports_Controller extends Mobile_Controller {

    public function __construct()
    {
		parent::__construct();
	}
	
	/**
	 * Displays a list of reports
	 * @param boolean $category_id If category_id is supplied filter by
	 * that category
	 */
	public function index($category_id = false)
	{
		$this->template->content = new View('mobile/reports');
		
		$db = new Database;
		
		$filter = ( $category_id )
			? " AND ( c.id='".$category_id."' OR 
				c.parent_id='".$category_id."' )  "
			: " AND 1 = 1";
			
		// Pagination
		$pagination = new Pagination(array(
				'style' => 'mobile',
				'query_string' => 'page',
				'items_per_page' => (int) Kohana::config('mobile.items_per_page'),
				'total_items' => $db->query("SELECT DISTINCT i.* FROM `".$this->table_prefix."incident` AS i JOIN `".$this->table_prefix."incident_category` AS ic ON (i.`id` = ic.`incident_id`) JOIN `".$this->table_prefix."category` AS c ON (c.`id` = ic.`category_id`) WHERE `incident_active` = '1' $filter")->count()
				));
		$this->template->content->pagination = $pagination;

		$incidents = $db->query("SELECT DISTINCT i.*, l.location_name FROM `".$this->table_prefix."incident` AS i JOIN `".$this->table_prefix."incident_category` AS ic ON (i.`id` = ic.`incident_id`) JOIN `".$this->table_prefix."category` AS c ON (c.`id` = ic.`category_id`) JOIN `".$this->table_prefix."location` AS l ON (i.`location_id` = l.`id`) WHERE `incident_active` = '1' $filter ORDER BY incident_date DESC LIMIT ". (int) Kohana::config('mobile.items_per_page') . " OFFSET ".$pagination->sql_offset);
		
		// If Category Exists
		if ($category_id)
		{
			$category = ORM::factory("category", $category_id);
		}
		else
		{
			$category = FALSE;
		}
			
		$this->template->content->filter = false;
		$this->template->content->incidents = $incidents;
		$this->template->content->category = $category;
		$this->template->content->have_results = true;
		
		$this->template->header->breadcrumbs = "&nbsp;&raquo;&nbsp;<a href=\"".url::site()."mobile/categories/\">Categories</a>";
	}
	
	public function search($town = false)
	{
		$location = null;
		$town = null;
		
		$this->template->content = new View('mobile/reports');
		
		// Force Home breadcrumb
		$this->template->header->breadcrumbs = " ";
		
		$db = new Database;
		
		$town = isset($_GET['town']) ? $_GET['town'] : '';
		$categoryid = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
		$distance = isset($_GET['distance']) ? (float)$_GET['distance'] : 0.5;
		$order = isset($_GET['order']) ? $_GET['order'] : '';
		switch ($order) {
		case 'date':
			$order = 'coalesce(incident_datemodify, incident_dateadd) desc';
			break;
		case 'verified':
			$order  = 'incident_verified desc';
			break;
		default:
			$order = 'distance asc';
		}

		if (!empty($town)) {
			$location = $town;
			if ( ! Kohana::config('settings.multi_country') && $country = ORM::factory('country', Kohana::config('settings.default_country'))->country)
			{
				$location .= ', '.$country;
			}
			$location = mobile_geocoder::geocode($location);
		}

		// if we don't get location there will be no results rendered
		if (!$location) {
			$this->template->content->have_results = false;
			$this->template->content->filter = true;
			$this->template->content->town = $town;
			$this->template->content->category = 0;
			$this->template->content->categories = array();
			return;
		}

		$fields = '
			69.09 *
			DEGREES(
				ACOS(
					SIN( RADIANS(latitude) )*SIN( RADIANS(' . $location['lat'] . ') )
				+
					COS( RADIANS(latitude) )*COS( RADIANS(' . $location['lat'] . ') )
				*
					COS( RADIANS(longitude - (' . $location['lon'] . ')) )
				)
			) as distance,
			i.*,coalesce(incident_datemodify, incident_dateadd) as last_updated, l.location_name';

		$where = '
			WHERE `incident_active` = 1
			';

		$having = "
			HAVING distance < $distance
			";

		$incidents_sql = "SELECT $fields
			FROM `".$this->table_prefix."incident` AS i
				JOIN `" . $this->table_prefix . "location` AS l ON (i.`location_id` = l.`id`)";

		if (!empty($categoryid)) {
			$incidents_sql .= "
				JOIN `" . $this->table_prefix . "incident_category` AS ic ON (i.`id` = ic.`incident_id`)
				";
			$where .= "AND  ic.category_id = $categoryid ";
		}
		$incidents_sql .= $where . $having;

		$pagination = new Pagination(array(
				'style' => 'mobile',
				'query_string' => 'page',
				'items_per_page' => (int) Kohana::config('mobile.items_per_page'),
				'total_items' => $db->query($incidents_sql)->count()
		));

		$this->template->content->pagination = $pagination;

		$incidents = $db->query(
			$incidents_sql .
				"ORDER BY $order
				 LIMIT ". (int) Kohana::config('mobile.items_per_page') . "
				 OFFSET {$pagination->sql_offset}"
		);

		$this->template->content->filter = true;
		$this->template->content->have_results = true;
		$this->template->content->town = $town;
		$this->template->content->incidents = $incidents;
		$this->template->content->category = null;
		
		$categories = ORM::factory('category')
		    ->where('category_visible', '1')
		    ->where('parent_id', '0')
		    ->where('category_trusted != 1')
		    ->orderby('category_title', 'ASC')
		    ->find_all();
		
		$this->template->content->categories = $categories;
	}	
	
	/**
	 * Displays a report.
	 * @param boolean $id If id is supplied, a report with that id will be
	 * retrieved.
	 */
	public function view($id = false)
	{	
		$this->template->header->show_map = TRUE;
		$this->template->header->js = new View('mobile/reports_view_js');
		$this->template->content = new View('mobile/reports_view');
		
		$this->template->header->breadcrumbs = " ";
		
		if ( ! $id )
		{
			url::redirect('mobile');
		}
		else
		{
			$incident = ORM::factory('incident', $id);
			if ( ! $incident->loaded)
			{
				url::redirect('mobile');
			}
			
			$this->template->content->incident = $incident;
			
			$page_no = (isset($_GET['p'])) ? $_GET['p'] : "";
			$category_id = (isset($_GET['c'])) ? $_GET['c'] : "";
			if ($category_id)
			{
				$category = ORM::factory('category')
					->find($category_id);
				if ($category->loaded)
				{
					$this->template->header->breadcrumbs = "&nbsp;&raquo;&nbsp;<a href=\"".url::site()."mobile/categories/\">Categories</a>&nbsp;&raquo;&nbsp;<a href=\"".url::site()."mobile/reports/index/".$category_id."?page=".$page_no."\">".$category->category_title."</a>";
				}
			}
		}
	}
	
	public function submit($id = FALSE, $saved = false)
	{
		// Cacheable Controller
		$this->is_cachable = FALSE;
		
		$this->template->header->show_map = FALSE;
		$this->template->content  = new View('mobile/reports_submit');
		
		$this->template->header->breadcrumbs = " ";
		
		$db = new Database();

		// First, are we allowed to submit new reports?
		if ( ! Kohana::config('settings.allow_reports'))
		{
			url::redirect(url::site().'main');
		}

		//Retrieve API URL
		$this->template->api_url = Kohana::config('settings.api_url');

		// Setup and initialize form field names
		$form = array(
			'incident_title' => '',
			'incident_description' => '',
			'incident_date' => '',
			'incident_hour' => '',
			'incident_minute' => '',
			'incident_ampm' => '',
			'latitude' => '',
			'longitude' => '',
			'geometry' => array(),
			'location_name' => '',
			'country_id' => '',
			'country_name'=>'',
			'incident_category' => array(),
			'incident_news' => array(),
			'incident_video' => array(),
			'incident_photo' => array(),
			'incident_zoom' => '',
			'person_first' => '',
			'person_last' => '',
			'person_email' => '',
			'form_id'	  => '',
			'custom_field' => array()
		);

		// Copy the form as errors, so the errors will be stored with keys corresponding to the form field names
		$errors = $form;
		$form_error = FALSE;

		$form_saved = ($saved == 'saved');

		// Initialize Default Values
		$form['incident_date'] = date("m/d/Y",time());
		$form['incident_hour'] = date('g');
		$form['incident_minute'] = date('i');
		$form['incident_ampm'] = date('a');
		$form['country_id'] = Kohana::config('settings.default_country');

		// Initialize Default Value for Hidden Field Country Name, just incase Reverse Geo coding yields no result
		$country_name = ORM::factory('country',$form['country_id']);
		$form['country_name'] = $country_name->country;

		// Initialize custom field array
		$form['custom_field'] = customforms::get_custom_form_fields($id,'',true);

		//GET custom forms
		$forms = array();
		foreach (customforms::get_custom_forms() as $custom_forms)
		{
			$forms[$custom_forms->id] = $custom_forms->form_title;
		}

		$this->template->content->forms = $forms;


		// Check, has the form been submitted, if so, setup validation
		if ($_POST)
		{
			// Geocode Location
			$geocode_error = FALSE;
			if ( empty($_POST['latitude']) AND empty($_POST['longitude']) 
				AND ! empty($_POST['location_name']) )
			{
				$default_country = Kohana::config('settings.default_country');
				$country_name = "";
				if ($default_country)
				{
					$country = ORM::factory('country', $default_country);
					if ($country->loaded)
					{
						$country_name = $country->country;
					}
				}
				
				$geocode = mobile_geocoder::geocode($_POST['location_name'].", ".$country_name);
				if ($geocode)
				{
					$_POST['latitude'] = $geocode['lat'];
					$_POST['longitude'] = $geocode['lon'];
				}
				else
				{
					$geocode_error = TRUE;
				}
			}
			
			// Instantiate Validation, use $post, so we don't overwrite $_POST fields with our own things
			$post = array_merge($_POST, $_FILES);

			// Test to see if things passed the rule checks
			if (reports::validate($post))
			{

				// STEP 1: SAVE LOCATION
				$location = new Location_Model();
				reports::save_location($post, $location);

				// STEP 2: SAVE INCIDENT
				$incident = new Incident_Model();
				reports::save_report($post, $incident, $location->id);

				// STEP 2b: SAVE INCIDENT GEOMETRIES
				reports::save_report_geometry($post, $incident);

				// STEP 3: SAVE CATEGORIES
				reports::save_category($post, $incident);

				// STEP 4: SAVE MEDIA
				//reports::save_media($post, $incident);

				// STEP 5: SAVE CUSTOM FORM FIELDS
				reports::save_custom_fields($post, $incident);

				// STEP 6: SAVE PERSONAL INFORMATION
				reports::save_personal_info($post, $incident);

				// Action::report_add/report_submit - Added a New Report
				//++ Do we need two events for this? Or will one suffice?
				//ETHERTON: Yes. Those of us who often write plugins for
				//Ushahidi would like to have access to the $post arrays
				//and the report object. Back in the day we even had access
				//to the $post object, so if our plugins didn't get the
				//appropriate input we could raise an error, but alas,
				//those days are gone. Now I suppose you could do something
				//like Event::run('ushahidi_action.report_add', array($post, $incident));
				//but for the sake of backward's compatibility, please don't
				//Thanks.
				Event::run('ushahidi_action.report_submit', $post);
				Event::run('ushahidi_action.report_add', $incident);


				url::redirect('mobile/reports/thanks');
			}

			// No! We have validation errors, we need to show the form again, with the errors
			else
			{
				if ($geocode_error)
				{
					$post->add_error('location_name', 'geocode');
				}
				
				// Repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

				// Populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('mobile_report'));
				
				// Ignore lat/lon errors
				if ($geocode_error)
				{
					if (isset($errors['latitude'])) unset($errors['latitude']);
					if (isset($errors['longitude'])) unset($errors['longitude']);
				}
				
				$form_error = TRUE;
			}
		}

		// Retrieve Country Cities
		$default_country = Kohana::config('settings.default_country');
		$this->template->content->cities = $this->_get_cities($default_country);
		$this->template->content->multi_country = Kohana::config('settings.multi_country');

		$this->template->content->id = $id;
		$this->template->content->form = $form;
		$this->template->content->errors = $errors;
		$this->template->content->form_error = $form_error;

		$categories = $this->_get_categories($form['incident_category']);
		$this->template->content->categories = $categories;

		// Pass timezone
		$this->template->content->site_timezone = Kohana::config('settings.site_timezone');

		// Pass the submit report message
		$this->template->content->site_submit_report_message = Kohana::config('settings.site_submit_report_message');

		// Retrieve Custom Form Fields Structure
		$this->template->content->custom_forms = new View('reports_submit_custom_forms');
		$disp_custom_fields = customforms::get_custom_form_fields($id,$form['form_id'], FALSE);
		$this->template->content->disp_custom_fields = $disp_custom_fields;
		$this->template->content->stroke_width_array = $this->_stroke_width_array();
		$this->template->content->custom_forms->disp_custom_fields = $disp_custom_fields;
		$this->template->content->custom_forms->form = $form;

		$this->template->header->js = new View('mobile/reports_submit_js');
	}
	
	// http://ushahidi.dev/mobile
	public function thanks()
		{
			$this->template->content = new View('mobile/reports_thanks');
			$this->template->header->breadcrumbs = "&nbsp;&raquo;&nbsp;<a href=\"".url::site()."mobile/reports/submit/\">Submit A Report</a>";
		}
	
	/*
	 * Retrieves Categories
	 */
	private function _get_categories($selected_categories)
	{
		$categories = ORM::factory('category')
			->where('category_visible', '1')
			->where('parent_id', '0')
			->where('category_trusted != 1')
			->orderby('category_position', 'ASC')
			->orderby('category_title', 'ASC')
			->find_all();

		return $categories;
	}
	
	
	/*
	 * Retrieves Cities
	 */
	private function _get_cities()
	{
		$cities = ORM::factory('city')->orderby('city', 'asc')->find_all();
		$city_select = array('' => 'Select A Location from the Drop-Down');

		foreach ($cities as $city)
		{
			$city_select[$city->city_lon.",".$city->city_lat] = $city->city;
		}

		return $city_select;
	}

	/**
	 * Array with Geometry Stroke Widths
    */
	private function _stroke_width_array()
	{
		for ($i = 0.5; $i <= 8 ; $i += 0.5)
		{
			$stroke_width_array["$i"] = $i;
		}

		return $stroke_width_array;
	}
}
