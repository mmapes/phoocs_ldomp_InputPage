<?php
class InputPage
{
	const CHOOSER    = 0;
	const ALL_JSON   = 1;
	const STAY_HERE  = 2;
	const CLOSE_SELF = 3;
	public $dataObjectClass = ""; 	// e.g., public $dataObjectClass = "DO_movie";

	protected function goChooser() { /* e.g., header("Location: choose_movie.php"); */ }
	protected function getPostedObject() 
	{
		/* e.g., 
		$new_DO = new $this->dataObjectClass();
		$new_DO->idMovie        = getRequest('movie_idMovie', 'POST', ''); 
		$new_DO->Title          = getRequest('movie_Title', 'POST', '');
		$new_DO->TitleNoArticle = getRequest('movie_TitleNoArticle', 'POST', '');

		// ... 

		$new_DO->ReleaseDate    = getRequest('movie_ReleaseDate', 'POST', '');
		return $new_DO;
		*/
	}

	function __construct()
	{
		$this->update = false;	// change to true if we are updating instead of inserting

		$this->afterInsert = self::CHOOSER;

		$this->new_DO = new $this->dataObjectClass();

		$this->pks = $this->new_DO->getPrimaryKeys();
		
		// if form has been submitted, insert the contact
		if (
			getRequest('Insert', 'POST', '') == 'Submit' ||
			getRequest('CreateCopy', 'POST', '') == 'Create a copy'
		   ) 
		{
			$this->updateAfterInsert();
			$success = $this->insertPostedObject();
			$this->doAfterInsert();
		}

		// if UPDATE form has been submitted, update the contact
		if (getRequest('Update', 'POST', '') == 'Submit') 
		{
			$this->updateAfterInsert();
			$success = $this->updatePostedObject();
			$this->doAfterInsert();
		}
		
		if (getRequest('UpdatePartial', 'POST', '') == 'Submit') 
		{
			$this->updateAfterInsert();
			$success = $this->partialUpdatePostedObject();
			$this->doAfterInsert();
		}
		
		if (getRequest('Delete', 'POST', '') == 'Delete') 
		{
			$this->updateAfterInsert();
			$success = $this->deletePostedObject();
			$this->doAfterInsert();
		}

		// if we've been passed a contact id, pre-fill the form
		foreach ($this->pks as $pk) 
		{
			if (getRequest($pk, 'GET', '') != '') 
			{
				$this->prefillForm();
				break;
			}
		}
	}
	
	public function prefillForm()
	{
		//$this->loc_idDO = getRequest($this->pk, 'BOTH', '');
		$this->update = true;
		$this->our_DO = new $this->dataObjectClass();
		foreach ($this->pks as $pk) 
		{
			$this->our_DO->$pk = getRequest($pk, 'BOTH', '');
		}
		$this->our_DO->get();
	}

	public function doAfterInsert()
	{
		if ($this->afterInsert == self::CHOOSER)
		{
			$this->goChooser();
		}
		if ($this->afterInsert == self::ALL_JSON)
		{
			$this->writeAllDataObjectsJson();
		}
		if ($this->afterInsert == self::STAY_HERE)
		{
			// $this->prefillForm();	// misleading - looks like we preload the page but we really don't
			$this->our_DO = $this->getPostedObject();
		}
		if ($this->afterInsert == self::CLOSE_SELF)
		{
			$this->our_DO = $this->closeSelf();
		}
	}
	
	public function writeAllDataObjectsJson()
	{
		$o = new $this->dataObjectClass();
		?>
		<script>
		if (window.opener && window.opener.maintainer && window.opener.maintainer.afterPopup)
		{
			window.opener.maintainer.afterPopup(<?php echo $o->findJson(); ?>);
			window.close();
		}
		</script>
		<?php
		exit;
	}

	protected function closeSelf()
	{
		$o = new $this->dataObjectClass();
		?>
		<script>
		if (window.opener)
		{
			window.close();
		}
		</script>
		<?php
		exit;
	}
	
	public function setAfterInsert($action)
	{
		if (
			$action == self::CHOOSER || 
			$action == self::ALL_JSON || 
			$action == self::STAY_HERE ||
			$action == self::CLOSE_SELF
		   )
		{
			$this->afterInsert = $action;
		}
		return $this->afterInsert;
	}
	
	public function updateAfterInsert()
	{
		if (getRequest('afterSubmit', 'POST', '') != '')
		{
			$this->setAfterInsert(getRequest('afterSubmit', 'POST', ''));
		}
	}
	
	protected function insertPostedObject()
	{
		$this->new_DO = $this->getPostedObject();
		return $this->new_DO->insert();
	}
	
	protected function updatePostedObject()
	{
		$this->new_DO = $this->getPostedObject();
		$this->new_DO->update();
	}
	
	protected function partialUpdatePostedObject()
	{
		$this->new_DO = $this->getPostedObject();
		$this->new_DO->updatePartial();
	}
	
	protected function deletePostedObject()
	{
		$this->new_DO = $this->getPostedObject();
		$this->new_DO->del();
	}
}
