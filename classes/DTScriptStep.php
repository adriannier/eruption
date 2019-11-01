<?php
	
class DTScriptStep extends DTElement {

	public $hiddenAttributes = ['id', 'name', 'enable'];
	
	public $childPaths = [
		'ParameterValues' => 'DTParameterValues',
		'Options' => 'DTParameter'
	];
	

	function description() {
		
		if (!isset($this->preparedChildDescriptions['Options'])) {
						
			$this->addChildDescription('Options', function() {
				
				if (!is_null($options = $this->firstChild('Options'))) {
	
					if (empty($options->attr('value'))) {
						return false;
					}
								
				}
				
			});
		
		}
				
		$description = parent::description();

		return $description;
		
	}
	
	function descriptionMethodName() {
		
		$methodName = '_'.preg_replace("/[^a-zA-Z0-9_]+/", "", str_replace(' ', '_', $this->name()));
		
		If ($methodName == '__comment') {
			$methodName = '_Comment';
		}
	
		return $methodName;
			
	}
	
	function nameForDescription() {
		
		if ($this->hideNameInDescription) {
			return false;
		
		} else if ($this->name() == '# (comment)') {
			return '#';
			
		} else if ($this->getAttribute('enable') == 'False') {
			return '// '.$this->name();
			
		} else {
			return $this->name();	
			
		}
		
		
	}
	
	function increasesIndentation() {
		
		return in_array($this->name(), [
			'If',
			'Loop',
			'Else If',
			'Else'
		]);
		
	}
	
	function decreasesIndentation() {
		
		return in_array($this->name(), [
			'End If',
			'End Loop',
			'Else If',
			'Else'
		]);
		
	}
		
	function _Adjust_Window() {

		

	}

	function _Allow_User_Abort() {

		

	}

	function _AVPlayer_Play() {

		

	}

	function _Beep() {

		

	}

	function _Close_File() {

		

	}

	function _Close_Popover() {

		

	}

	function _Close_Window() {

		

	}

	function _Comment() {

		$this->addChildDescription('ParameterValues', function() {
			
			if (!is_null($comment = $this->firstChild('ParameterValues/Parameter/Comment'))) {
				
				$text = $comment->text();
				
				if (empty($text)) {
					$this->hideNameInDescription = true;
					return false;
				} else {
					return '# '.$text;
				}
							
			}
			
		});

	}

	function _Commit_RecordsRequests() {

		

	}

	function _Constrain_Found_Set() {

		

	}

	function _Copy() {

		

	}

	function _Delete_All_Records() {

		

	}

	function _Delete_Portal_Row() {

		

	}

	function _Delete_RecordRequest() {

		

	}

	function _Dial_Phone() {

		

	}

	function _Duplicate_RecordRequest() {

		

	}

	function _Else() {

		

	}

	function _Else_If() {

		

	}

	function _End_If() {

		

	}

	function _End_Loop() {

		

	}

	function _Enter_Browse_Mode() {

		

	}

	function _Enter_Find_Mode() {

		

	}

	function _Enter_Preview_Mode() {

		

	}

	function _Exit_Loop_If() {

		

	}

	function _Exit_Script() {

		

	}

	function _Export_Field_Contents() {

		

	}

	function _Export_Records() {

		

	}

	function _Extend_Found_Set() {

		

	}

	function _Freeze_Window() {

		

	}

	function _Get_Folder_Path() {

		

	}

	function _Go_to_Field() {

		

	}

	function _Go_to_Layout() {

		

	}

	function _Go_to_Next_Field() {

		

	}

	function _Go_to_Object() {

		

	}

	function _Go_to_Portal_Row() {

		

	}

	function _Go_to_Previous_Field() {

		

	}

	function _Go_to_RecordRequestPage() {

		

	}

	function _Go_to_Related_Record() {

		

	}

	function _Halt_Script() {

		

	}

	function _If() {

		

	}

	function _Import_Records() {

		

	}

	function _Insert_Calculated_Result() {

		

	}

	function _Insert_File() {

		

	}

	function _Insert_from_Device() {

		

	}

	function _Insert_from_URL() {

		

	}

	function _Insert_PDF() {

		

	}

	function _Insert_Picture() {

		

	}

	function _Install_Menu_Set() {

		

	}

	function _Install_OnTimer_Script() {

		

	}

	function _Install_PlugIn_File() {

		

	}

	function _Loop() {

		

	}

	function _Modify_Last_Find() {

		

	}

	function _MoveResize_Window() {

		

	}

	function _New_RecordRequest() {

		

	}

	function _New_Window() {

		

	}

	function _Omit_Multiple_Records() {

		

	}

	function _Omit_Record() {

		

	}

	function _Open_File_Options() {

		

	}

	function _Open_Manage_Database() {

		

	}

	function _Open_Manage_Layouts() {

		

	}

	function _Open_Manage_Value_Lists() {

		

	}

	function _Open_Script_Workspace() {

		

	}

	function _Open_URL() {

		

	}

	function _PauseResume_Script() {

		

	}

	function _Perform_AppleScript() {

		

	}

	function _Perform_Find() {

		

	}

	function _Perform_Script() {

		

	}

	function _Perform_Script_on_Server() {

		

	}

	function _Print() {

		

	}

	function _Print_Setup() {

		

	}

	function _Refresh_Portal() {

		

	}

	function _Refresh_Window() {

		

	}

	function _ReLogin() {

		

	}

	function _Replace_Field_Contents() {

		

	}

	function _Save_Records_as_PDF() {

		

	}

	function _Scroll_Window() {

		

	}

	function _Select_All() {

		

	}

	function _Select_Window() {

		

	}

	function _Send_Mail() {

		

	}

	function _Set_Error_Capture() {

		

	}

	function _Set_Field() {

		

	}

	function _Set_Field_By_Name() {

		

	}

	function _Set_Layout_Object_Animation() {

		

	}

	function _Set_Selection() {

		

	}

	function _Set_Variable() {

		$this->addChildDescription('Options', function() {
			
			if (!is_null($options = $this->firstChild('Options'))) {

				if ($options->attr('value') == '16388') {
					return false;
				}
							
			}
			
		});
	
	}

	function _Set_Web_Viewer() {

		

	}

	function _Set_Window_Title() {

		

	}

	function _Set_Zoom_Level() {

		

	}

	function _ShowHide_Menubar() {

		

	}

	function _ShowHide_Text_Ruler() {

		

	}

	function _ShowHide_Toolbars() {

		

	}

	function _Show_All_Records() {

		

	}

	function _Show_Custom_Dialog() {

		

	}

	function _Show_Omitted_Only() {

		

	}

	function _Sort_Records() {

		

	}

	function _Speak() {

		

	}

	function _Unsort_Records() {

		

	}

	function _View_As() {

		

	}
		
}