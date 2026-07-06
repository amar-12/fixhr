var tabBtn1 = document.getElementById("tab1btns");
var tabBtn2 = document.getElementById("tab2btns");
var tabBtn3 = document.getElementById("tab3btns");
var tabBtn4 = document.getElementById("tab4btns");
var tabBtn5 = document.getElementById("tab5btns");
var tabBtn6 = document.getElementById("tab6btns");
var tabBtn7 = document.getElementById("tab7btns");
var tabBtn8 = document.getElementById("tab8btns");
var tabBtn9 = document.getElementById("tab9btns");
var tabBtn10 = document.getElementById("tab10btns");

var tab1s = document.getElementById("tab1");
var tab2s = document.getElementById("tab2");
var tab3s = document.getElementById("tab3");
var tab4s = document.getElementById("tab4");
var tab5s = document.getElementById("tab5");
var tab6s = document.getElementById("tab6");
var tab7s = document.getElementById("tab7");
var tab8s = document.getElementById("tab8");
var tab9s = document.getElementById("tab9");
var tab10s = document.getElementById("tab10");

var tabCard1s = document.getElementById("tabCard1");
var tabCard2s = document.getElementById("tabCard2");
var tabCard3s = document.getElementById("tabCard3");
var tabCard4s = document.getElementById("tabCard4");
var tabCard5s = document.getElementById("tabCard5");
var tabCard6s = document.getElementById("tabCard6");
var tabCard7s = document.getElementById("tabCard7");
var tabCard8s = document.getElementById("tabCard8");
var tabCard9s = document.getElementById("tabCard9");
var AvtarDiv = document.getElementById("avtarDiv");

var IDemployee_id = document.getElementById("employee_id");
var IDprimary_id = document.getElementById("primary_id");

var IDEmpIdError = document.getElementById("EmpIdError");
var IDprefix = document.getElementById("prefix");
var IDfirstName = document.getElementById("firstName");
var IDfirstNameErrorShow = document.getElementById("firstNameErrorShow");
var IDmiddleName = document.getElementById("middleName");
var IDmiddleNameError = document.getElementById("middleNameError");
var IDlastName = document.getElementById("lastName");
var IDlastNameError = document.getElementById("lastNameError");
// var IDcountryCode = document.getElementById('countryCode');
var IDcontact = document.getElementById("contact");
var IDcontactError = document.getElementById("contactError");
var IDemail = document.getElementById("email");
var IDemailError = document.getElementById("emailError");
var IDdateOfBirth = document.getElementById("dateOfBirth");
var IDdateOfBirthError = document.getElementById("dateOfBirthError");
var IDgender = document.getElementById("gender");
var IDgenderError = document.getElementById("genderError");
var IDmariteStatus = document.getElementById("mariteStatus");
var IDmariteStatusError = document.getElementById("mariteStatusError");
var IDbloodGroup = document.getElementById("bloodGroup");
var IDbloodGroupError = document.getElementById("bloodGroupError");

var IDstatus = document.getElementById("status");
var IDstatusError = document.getElementById("statusError");
var IDcontractType = document.getElementById("contractType");
var IDcontractTypeError = document.getElementById("contractTypeError");
var IDemployeeJobStatus = document.getElementById("employeeJobStatus");
var IDemployeeJobStatusError = document.getElementById("employeeJobStatusError");
var IDdateOfJoin = document.getElementById("dateOfJoin");
var IDdateOfJoinError = document.getElementById("dateOfJoinError");
var IDdateOfGroupJoin = document.getElementById("dateOfGroupJoin");
var IDdateOfGroupJoinError = document.getElementById("dateOfGroupJoinError");
var IDdateOfGratuity = document.getElementById("dateOfGratuity");
var IDdateOfGratuityError = document.getElementById("dateOfGratuityError");
var IDdateOfTransfer = document.getElementById("dateOfTransfer");
var IDdateOfTransferError = document.getElementById("dateOfTransferError");
var IDdateOfExpectedConfirmation = document.getElementById(
    "dateOfExpectedConfirmation"
);
var IDdateOfExpectedConfirmationError = document.getElementById(
    "dateOfExpectedConfirmationError"
);
var IDprobationPeriod = document.getElementById("probationPeriod");
var IDprobationPeriodError = document.getElementById("probationPeriodError");
var IDdateOfConfirmation = document.getElementById("dateOfConfirmation");
var IDdateOfConfirmationError = document.getElementById(
    "dateOfConfirmationError"
);
var IDdateOfPayStructure = document.getElementById("dateOfPayStructure");
var IDdateOfPayStructureError = document.getElementById(
    "dateOfPayStructureError"
);

var IDesic_limit = document.getElementById("esic_limit");
var IDpf_enable = document.getElementById("pf_enable");
var IDbranch = document.getElementById("branch");
var IDbranchError = document.getElementById("branchError");
var IDdepartment = document.getElementById("department");
var IDdepartmentError = document.getElementById("departmentError");
var IDdesignation = document.getElementById("designation");
var IDdesignationError = document.getElementById("designationError");
var IDgradeTADA = document.getElementById("gradeTADA");
var IDgradeTADAError = document.getElementById("gradeTADAError");
var IDrole = document.getElementById("role");
var IDroleError = document.getElementById("roleError");
var IDreportManager = document.getElementById("reporting_manager");
var IDreportManagerError = document.getElementById("reportManagerError");
var IDbudgetCode = document.getElementById("budgetCode");
var IDbudgetCodeError = document.getElementById("budgetCodeError");

var IDcompanyName = document.getElementById("companyName");
var IDcompanyNameError = document.getElementById("companyNameError");
var IDdesignationName = document.getElementById("designationName");
var IDdesignationNameError = document.getElementById("designationNameError");
var IDjoiningPeriod = document.getElementById("joinDatecompany");
var IDjoiningPeriodError = document.getElementById("joiningPeriodError");

var IDleavingPeriod = document.getElementById("leaveDatecompany");
var IDserviceDuration = document.getElementById("serviceDuration");

var IDpo = document.getElementById("po_id");

var IDcompanyName2 = document.getElementById("companyName2");
var IDcompanyNameError2 = document.getElementById("companyNameError2");
var IDdesignationName2 = document.getElementById("designationName2");
var IDdesignationNameError2 = document.getElementById("designationNameError2");
var IDjoiningPeriod2 = document.getElementById("joinDatecompany1");
var IDjoiningPeriodError2 = document.getElementById("joiningPeriodError2");

var IDleavingPeriod2 = document.getElementById("leaveDatecompany1");
var IDserviceDuration2 = document.getElementById("serviceDuration1");

var IDpo2 = document.getElementById("po_id2");

var IDattendanceMethod = document.getElementById("attendanceMethod");
var IDattendanceMethodError = document.getElementById("attendanceMethodError");
var IDcheckInMethod = document.getElementsByName("checkInMethod[]");
var masterVal = [];
var IDcheckInMethodError = document.getElementById("checkInMethodError");
var IDasignSetup = document.getElementById("asignSetup");
var IDasignSetupError = document.getElementById("asignSetupError");
var IDasignShift = document.getElementById("asignShift");
var IDasignShiftError = document.getElementById("asignShiftError");
var IDattendancePolicy = document.getElementById("attendancePolicy");
var IDgeofencingId = document.getElementById("geofencingId");
var IDweekOffId = document.getElementById("weekOffId");
var IDassigngeobranch = document.getElementById("assign_geo_branch");
// Get the checked radio button by name
var IDattendancePreference = document.querySelector('input[name="emp_attendance_preference"]:checked');
// Check if a radio button is selected
if (IDattendancePreference) {
    var label = document.querySelector(`label[for="${IDattendancePreference.id}"]`);

    // Get the text of the label
    var labelText = label ? label.textContent.trim() : null;

}
var IDattendancePreferenceError = document.getElementById("attendancePreferenceErrorID");
var IDattendancePolicyError = document.getElementById("attendancePolicyError");
var IDgeofencingIdError = document.getElementById("geofencingIdError");
var IDweekOffIdError = document.getElementById("weekOffIdError");
var IDleavePolicy = document.getElementById("leavePolicy");
var IDleavePolicyError = document.getElementById("leavePolicyError");
var IDjoiningLeave = document.getElementById("joiningLeave");
var IDjoiningLeaveError = document.getElementById("joiningLeaveError");
var IDcalculationMethod = document.getElementById("calculationMethod");
var IDcalculationMethodError = document.getElementById("calculationMethodError");
var IDapplicableDate = document.getElementById("applicableDate");
var IDapplicableDateError = document.getElementById("applicableDateError");
var IDprobationLeave = document.getElementById("probationLeave");
var IDprobationLeaveError = document.getElementById("probationLeaveError");
var IDaadhar_number = document.getElementById("aadhar_number");
var IDaadhar_numberError = document.getElementById("aadhar_numberError");
var IDupload_aadhar = document.getElementById("upload_aadhar");
var IDupload_aadharError = document.getElementById("upload_aadharError");
var IDdrivng_license_number = document.getElementById("drivng_license_number");
var IDdrivng_license_numberError = document.getElementById(
    "drivng_license_numberError"
);
var IDupload_drivng_license = document.getElementById("upload_drivng_license");
var IDupload_drivng_licenseError = document.getElementById(
    "upload_drivng_licenseError"
);
var IDvoter_id_number = document.getElementById("voter_id_number");
var IDvoter_id_numberError = document.getElementById("voter_id_numberError");
var IDupload_voter_id = document.getElementById("upload_voter_id");
var IDupload_voter_idError = document.getElementById("upload_voter_idError");
var IDpassport_number = document.getElementById("passport_number");
var IDpassport_numberError = document.getElementById("passport_numberError");
var IDupload_passport = document.getElementById("upload_passport");
var IDupload_passportError = document.getElementById("upload_passportError");
var IDaccount_number = document.getElementById("account_number");
var IDaccount_numberError = document.getElementById("account_numberError");
var IDupload_passbook = document.getElementById("upload_passbook");
var IDupload_passbookError = document.getElementById("upload_passbookError");
var IDpan_number = document.getElementById("pan_number");
var IDpan_numberError = document.getElementById("pan_numberError");
var IDupload_pan = document.getElementById("upload_pan");
var IDupload_panError = document.getElementById("upload_panError");
var IDpfTrustCode = document.getElementById("pfTrustCode");
var IDpfTrustCodeError = document.getElementById("pfTrustCodeError");
var IDpfFoundMember = document.getElementById("pfFoundMember");
var IDpfFoundMemberError = document.getElementById("pfFoundMemberError");
var IDPfNumber = document.getElementById("PfNumber");
var IDPfNumberError = document.getElementById("PfNumberError");
var IDuniversalAccountNumber = document.getElementById(
    "universalAccountNumber"
);
var IDuniversalAccountNumberError = document.getElementById(
    "universalAccountNumberError"
);
var IDvpfPercentage = document.getElementById("vpfPercentage");
var IDvpfPercentageError = document.getElementById("vpfPercentageError");
var IDpfDateOfJoining = document.getElementById("pfDateOfJoining");
var IDpfDateOfJoiningError = document.getElementById("pfDateOfJoiningError");
var IDpfDateOfLeaving = document.getElementById("pfDateOfLeaving");
var IDpfDateOfLeavingError = document.getElementById("pfDateOfLeavingError");
var IDreasonOfLeavingPF = document.getElementById("reasonOfLeavingPF");
var IDreasonOfLeavingPFError = document.getElementById(
    "reasonOfLeavingPFError"
);

// Group Insurance Inputs
var IDgroupInsuredBy = document.getElementById("emp_group_insured_by");
var IDgroupInsuranceNumber = document.getElementById("emp_group_insurance_no");
var IDgroupInsuranceStartDate = document.getElementById("emp_group_insurance_start_date");
var IDgroupInsuranceTillDate = document.getElementById("emp_group_insurance_till_date");

// Group Insurance Error Spans
var IDgroupInsuredByError = document.getElementById("groupInsuredByError");
var IDgroupInsuranceNumberError = document.getElementById("groupInsuranceNumberError");
var IDgroupInsuranceStartDateError = document.getElementById("groupInsuranceStartDateError");
var IDgroupInsuranceTillDateError = document.getElementById("groupInsuranceTillDateError");



var IDesiNumber = document.getElementById("esiNumber");
var IDesiNumberError = document.getElementById("esiNumberError");
var IDesiDespensary = document.getElementById("esiDespensary");
var IDesiDespensaryError = document.getElementById("esiDespensaryError");
var IDesiDateOfJoining = document.getElementById("esiDateOfJoining");
var IDesiDateOfJoiningError = document.getElementById("esiDateOfJoiningError");
var IDesiDateOfLeaving = document.getElementById("esiDateOfLeaving");
var IDesiDateOfLeavingError = document.getElementById("esiDateOfLeavingError");
var IDreasonOfLeavingESIC = document.getElementById("reasonOfLeavingESIC");
var IDreasonOfLeavingESICError = document.getElementById(
    "reasonOfLeavingESICError"
);
var primaryEQID = document.getElementById("qualification_primary_id");
var IDqualification = document.getElementById("qualification");
var IDstream = document.getElementById("stream");
var IDcource_type = document.getElementById("cource_type");
var IDspecalization = document.getElementById("specalization");
var IDcource_nature = document.getElementById("cource_nature");
var IDquali_status = document.getElementById("quali_status");
var IDinstitute_name = document.getElementById("institute_name");
var IDuniversityName = document.getElementById("universityName");
var IDedu_from_date = document.getElementById("edu_from_date");
var IDedu_to_date = document.getElementById("edu_to_date");
var IDpassing_date = document.getElementById("passing_date");
var IDpercentage = document.getElementById("percentage");
var IDgrade = document.getElementById("grade");
var IDduration = document.getElementById("duration");
var IDyear = document.getElementById("year");

var IDtempPinCode = document.getElementById("tempPinCode");
var IDtempPinCodeError = document.getElementById("tempPinCodeError");

var IDpermanentPinCode = document.getElementById("permanentPinCode");
var IDpermanentPinCodeError = document.getElementById("permanentPinCodeError");

var IDpermanentSearchInput = document.getElementById("permanentSearchInput");
var IDpermanentSearchInputError = document.getElementById(
    "permanentSearchInputError"
);
var IDpermanentLongitude = document.getElementById("permanentLongitude");
var IDpermanentLongitudeError = document.getElementById("permanentLongitudeError");
var IDpermanentLatitude = document.getElementById("permanentLatitude");
var IDpermanentLatitudeError = document.getElementById(
    "permanentLatitudeError"
);

var IDtemporarySearchInput = document.getElementById("temporarySearchInput");
var IDtemporarySearchInputError = document.getElementById(
    "temporarySearchInputError"
);
var IDtemporaryLongitude = document.getElementById("temporaryLongitude");
var IDtemporaryLongitudeError = document.getElementById(
    "temporaryLongitudeError"
);
var IDtemporaryLatitude = document.getElementById("temporaryLatitude");
var IDtemporaryLatitudeError = document.getElementById(
    "temporaryLatitudeError"
);

var IDyearOfService = document.getElementById("yearOfService");
var IDretirementDate = document.getElementById("retirementDate");
var IDseparationSubmitDate = document.getElementById("separationSubmitDate");
var IDexpectedLeavingDate = document.getElementById("expectedLeavingDate");
var IDleavingDateAsPerNoticePeriod = document.getElementById(
    "leavingDateAsPerNoticePeriod"
);
var IDnoticePeriodRequiredDate = document.getElementById(
    "noticePeriodRequiredDate"
);
var IDreasonForLeave = document.getElementById("reasonForLeave");
var IDleaveDate = document.getElementById("leaveDate");
var IDnoticePeriodServedDate = document.getElementById(
    "noticePeriodServedDate"
);
var IDsettlementFrom = document.getElementById("settlementFrom");
var IDfinalSettlementDate = document.getElementById("finalSettlementDate");
var IDnoticePeriodShortfallDays = document.getElementById(
    "noticePeriodShortfallDays"
);
var IDexitInterviewDate = document.getElementById("exitInterviewDate");
var IDlastWorkingDate = document.getElementById("lastWorkingDate");
var IDremark = document.getElementById("remark");
var IDemployerNoticePeriod = document.getElementById("employerNoticePeriod");
var IDemployeeNoticePeriod = document.getElementById("employeeNoticePeriod");
var IDifsc = document.getElementById("ifsc");
var IDaccountCode = document.getElementById("accountCode");
var IDaccountCodeError = document.getElementById("accountCodeError");
var IDIFSCError = document.getElementById("IFSCError");
var IDbankName = document.getElementById("bankName");
var IDbankNameError = document.getElementById("bankNameError");
var IDbranchName = document.getElementById("branchName");
var IDbranchNameError = document.getElementById("branchNameError");
var IDbank_account_number = document.getElementById("bank_account_number");
var IDbankaccountnumberError = document.getElementById("bankaccountnumberError");
var IDlast_sd = document.getElementById("last_sd");
var IDMICR = document.getElementById("micr");
var IDbranchCode = document.getElementById("branch_code");

var IDemployeeName = document.getElementById("employeeName");
var IDemployeeNameFinish = document.getElementById("employeeNameFinish");
var EmailText = document.getElementById("emailText");

let empIDText = document.getElementById("EmpIDText");
let contactText = document.getElementById("ContactText");
let birthdayText = document.getElementById("birthdayText");
let genderText = document.getElementById("genderText");
let martialText = document.getElementById("martialText");
let bloodGroupText = document.getElementById("bloodGrouptext");

let activeText = document.getElementById("activeText");
let contractText = document.getElementById("contractText");
let joiningDateText = document.getElementById("joiningDateText");
let jobStatusText = document.getElementById("jobStatusText");
let groupJoiningText = document.getElementById("groupJoiningText");
let gratuityDateText = document.getElementById("gratuityDateText");
let transferDateText = document.getElementById("transferDateText");
let expectedConfirmationText = document.getElementById(
    "expectedConfirmationText"
);
let probationDateText = document.getElementById("probationDateText");
let confirmationDateText = document.getElementById("confirmationDateText");
let payStructureDateText = document.getElementById("payStructureDateText");

let esicLimitText = document.getElementById("esicLimitText");
let pfLimitText = document.getElementById("pfLimitText");
let branchText = document.getElementById("branchText");
let departmentText = document.getElementById("departmentText");
let designationText = document.getElementById("designationText");
let gradeText = document.getElementById("gradeText");
let roleText = document.getElementById("roleText");
let reportManagerText = document.getElementById("reportManagerText");
let budgetCodeText = document.getElementById("budgetCodeText");

let companyNameText = document.getElementById("companyNameText");
let designationNameText = document.getElementById("designationNameText");
let joiningPeriodText = document.getElementById("joiningPeriodText");

let companyNameText2 = document.getElementById("companyNameText2");
let designationNameText2 = document.getElementById("designationNameText2");
let joiningPeriodText2 = document.getElementById("joiningPeriodText2");

let assignMethodText = document.getElementById("assignMethodText");
let checkInMethodText = document.getElementById("checkInMethodText");
let assignSetupText = document.getElementById("assignSetupText");
let shiftPolicyText = document.getElementById("shiftPolicyText");
let attendancePolicyText = document.getElementById("attendancePolicyText");
let geofencingText = document.getElementById("geofencingText");
let weekOffText = document.getElementById("weekOffText");
let attendancePreferenceText = document.getElementById("attendancePreferenceText");
let assignShift = document.getElementById("assignShift");
let leavePolicyText = document.getElementById("leavePolicyText");
let joiningLeaveText = document.getElementById("joiningLeaveText");
let calculationMethodText = document.getElementById("calculationMethodText");
let applicableDateText = document.getElementById("applicableDateText");
let probationLeaveText = document.getElementById("probationLeaveText");

let aadhardNumberText = document.getElementById("aadhardNumberText");
let drivingLicenseText = document.getElementById("drivingLicenseText");
let electionCardText = document.getElementById("electionCardText");
let passportText = document.getElementById("passportText");
let bankAcNumText = document.getElementById("bankAcNumText");
let panNumText = document.getElementById("panNumText");

var pftrustCode = document.getElementById("pftrustCodeText");
var pensionMember = document.getElementById("pensionMemberText");
var pfNum = document.getElementById("pfNumText"); // First occurrence of pfNumText
var uniAcNum = document.getElementById("uniAcNumText");
var vpfPerc = document.getElementById("vpfPercText");
var pfJoinDate = document.getElementById("pfJoinDateText");
var pfLeaveDate = document.getElementById("pfLeaveDateText");
var PfLeaveReason = document.getElementById("PfLeaveReasonText");
var esicNum = document.getElementById("esicNumText");
var esiDesperacy = document.getElementById("esiDesperacyText");
var esicDateOfJoining = document.getElementById("esicDateOfJoiningText");
var esicDateOfLeave = document.getElementById("esicDateOfLeaveText");
var esicLeaveReason = document.getElementById("esicLeaveReasonText");
var qualification = document.getElementById("qualificationText");
var stream = document.getElementById("streamText");
var courseType = document.getElementById("courseTypeText");
var specialization = document.getElementById("SpecializationText");
var natureOfCourse = document.getElementById("NatureofCourseText");
var qualificationStatus = document.getElementById("QualificationStatusText");
var instituteName = document.getElementById("InstituteNameText");
var universityName = document.getElementById("UniversityNameText");
var fromDate = document.getElementById("FromDateText");
var toDate = document.getElementById("ToDateText");
var passingDate = document.getElementById("PassingDateText");
var percentage = document.getElementById("PercentageText");
var grade = document.getElementById("GradeText");
var durationOfCourse = document.getElementById("DurationofCourseText");
var year = document.getElementById("YearText");

var yearOfServiceElement = document.getElementById("YearofServiceText");
var retirementDateElement = document.getElementById("RetirementDateText");
var separationSubmitOnElement = document.getElementById(
    "SeprationSubmitOnText"
);
var expectedLeavingDateElement = document.getElementById(
    "ExpectedLeavingDateText"
);
var leavingDatePeriodElement = document.getElementById("LeavingDatePeriodText");
var noticePeriodDaysElement = document.getElementById("NoticePeriodDaysText");
var reasonForLeavingElement = document.getElementById("ReasonForLeavingText");
var leavingDateElement = document.getElementById("LeavingDateText");
var noticeServedDaysElement = document.getElementById("NoticeServedDaysText");
var settlementFromElement = document.getElementById("SettlementFromText");
var finalSettlementDateElement = document.getElementById(
    "FinalSettlementDateText"
);
var noticePeriodShortfallDaysElement = document.getElementById(
    "NoticePeriodShorftfallDaysText"
);
var exitInterviewDateElement = document.getElementById("ExitInterviewDateText");
var lastWorkingDateElement = document.getElementById("LastWorkingDateText");
var remarkElement = document.getElementById("RemarkText");
var noticePeriodForEmployerElement = document.getElementById(
    "NoticePeriodForEmployerText"
);
var noticePeriodForEmployeeElement = document.getElementById(
    "NoticePeriodForEmployeeText"
);
var accountCodeText = document.getElementById("accountCodeText");
var ifscElement = document.getElementById("ifscText");
var bankNameElement = document.getElementById("BankNameText");
var branchNameElement = document.getElementById("BranchNameText");
var micrElement = document.getElementById("MICRText");
var bankCodeElement = document.getElementById("BankCodeText");
var bankAccountNumberElement = document.getElementById("BankAccountNumberText");
// var BankCodeTextElement = document.getElementById("BankCodeText");
var fileInput = document.getElementById("profileInput2");

// #GeoWork
var IDgeoworkId = document.getElementById("geoworkId");
var IDgeoworkIdError = document.getElementById("geoworkIdError");


// #About
var IDemp_official_contact = document.getElementById("emp_official_contact");
var IDemp_official_contact_error = document.getElementById("emp_official_contact_error");

var IDemp_official_email = document.getElementById("emp_official_email");
var IDemp_official_email_error = document.getElementById("emp_official_email_error");

var IDemp_emergency_relation = document.getElementById("emp_emergency_relation");
var IDemp_emergency_relation_error = document.getElementById("emp_emergency_relation_error");

var IDemp_emergency_contact = document.getElementById("emp_emergency_contact");
var IDemp_emergency_contact_error = document.getElementById("emp_emergency_contact_error");

// #Optional Info
var IDemp_nationality = document.getElementById("emp_nationality");
var IDemp_nationality_error = document.getElementById("emp_nationality_error");

var IDemp_religion = document.getElementById("emp_religion");
var IDemp_religion_error = document.getElementById("emp_religion_error");

var IDemp_category = document.getElementById("emp_category");
var IDemp_category_error = document.getElementById("emp_category_error");

var IDemp_body_mark = document.getElementById("emp_body_mark");
var IDemp_body_mark_error = document.getElementById("emp_body_mark_error");

// #Bank Details - Salary Account
var IDemp_salary_accountCode = document.getElementById("emp_salary_accountCode");
var IDaccountCodeError = document.getElementById("accountCodeError");

var IDemp_salary_ifsc = document.getElementById("emp_salary_ifsc");
var IDIFSCError = document.getElementById("IFSCError");

var IDemp_salary_bankName = document.getElementById("emp_salary_bankName");
var IDbankNameError = document.getElementById("bankNameError");

var IDemp_salary_branchName = document.getElementById("emp_salary_branchName");
var IDbranchNameError = document.getElementById("branchNameError");

var IDemp_salary_micr = document.getElementById("emp_salary_micr");
var IDemp_salary_micrerError = document.getElementById("emp_salary_micrerError");

var IDemp_salary_branch_code = document.getElementById("emp_salary_branch_code");
var IDemp_salary_branch_codeerError = document.getElementById("emp_salary_branch_codeerError");

var IDemp_salary_bank_account_number = document.getElementById("emp_salary_bank_account_number");
var IDbank_account_numberError = document.getElementById("bank_account_numberError");

// #Organization Information
var IDprofitCenter = document.getElementById("profitCenter");
var IDprofitCenterError = document.getElementById("profitCenterError");

var IDcostCenter = document.getElementById("costCenter");
var IDcostCenterError = document.getElementById("costCenterError");

var IDassignedRegion = document.getElementById("assignedRegion");
var IDassignedRegionError = document.getElementById("assignedRegionError");

// #Joining
var IDemp_project_assigned = document.getElementById("emp_project_assigned");
var IDemp_project_assigned_error = document.getElementById("emp_project_assigned_error");

// EPS Enable
var IDEmpEpsEnalbed = document.getElementById("eps_enabled");
var IDEmpEpsEnalbedError = document.getElementById("eps_error");

// Valid Thru
var IDEmpPassport = document.getElementById("emp_passport_valid");
var IDEmpdrivinglicense = document.getElementById("emp_drivng_license_valid");

//offline sync
var IDemp_offline_status = document.getElementById("emp_offline_status");
var IDemp_approval_manager = document.getElementById("is_approval_manager");

//Documents Uplode 
var IDEmpdocaadhar = document.getElementById("upload_aadhar");
var IDEmpdocdriving = document.getElementById("upload_drivng_license");
var IDEmpdocvoter = document.getElementById("upload_voter_id");
var IDEmpdocpassbook = document.getElementById("upload_passbook");
var IDEmpdocpassport = document.getElementById("upload_passport");
var IDEmpdocpan = document.getElementById("upload_pan");

// For Address // Permanent Address Elements
var permanentAddressElement = document.getElementById("permanentAddressText");
var permanentLongitudeElement = document.getElementById("permanentLongitudeText");
var permanentLatitudeElement = document.getElementById("permanentLatitudeText");
var permanentPinCodeElement = document.getElementById("permanentPinCodeText");

// Temporary Address Elements
var temporaryAddressElement = document.getElementById("temporaryAddressText");
var temporaryLongitudeElement = document.getElementById("temporaryLongitudeText");
var temporaryLatitudeElement = document.getElementById("temporaryLatitudeText");
var temporaryPinCodeElement = document.getElementById("temporaryPinCodeText");


// Get elements
let bankRadio = document.getElementById("bank");
let cashRadio = document.getElementById("cash");
let chequeRadio = document.getElementById("cheque");

let paymentMethodError = document.getElementById("payment_method");


// Salary Purpose
var Idaccountyype = document.getElementById("account_type");
var IdaccountYypeError = document.getElementById("account_typeError");

// employee / employer req days 
// for employee 
var IDemployeeNoticePeriod = document.getElementById("employeeNoticePeriod");
var IDemployeeNoticePeriodError = document.getElementById("employeeNoticePeriodError");

// for Employer
var IDemployerNoticePeriod = document.getElementById("employerNoticePeriod");
var IDemployerNoticePeriodError = document.getElementById("employerNoticePeriodError");



var file = fileInput.files.length > 0 ? fileInput.files[0] : null;

const imageTarget = document.getElementById("employee-avatar");
var avtarEmp = document.getElementById("empAvtar");
var tempSameAsParmanant = document.getElementById("tempSameAsParmanant");

function validAlpha(inputElement) {
    const regex = /^[A-Za-z]*$/;
    const value = inputElement.value;
    if (!regex.test(value)) {
        inputElement.value = value.replace(/[^A-Za-z]/g, ''); // Remove any non-alphabetical characters
    }
}

function namePrint(e) {
    var prefix = IDprefix.options[IDprefix.selectedIndex].text;
    IDemployeeName.innerHTML =
        prefix +
        " " +
        IDfirstName.value +
        " " +
        IDmiddleName.value +
        " " +
        IDlastName.value;
    IDemployeeNameFinish.innerHTML =
        prefix +
        " " +
        IDfirstName.value +
        " " +
        IDmiddleName.value +
        " " +
        IDlastName.value;
}
let activeTab = 1;

function updateErrorMessages2() {
 
        let tempValid = false;
        let permValid = false;

        // TEMP PIN
        if (!IDtempPinCode.value) {
            IDtempPinCodeError.innerHTML = "This field can't be empty.";
        } else if (IDtempPinCode.value.length !== 6) {
            IDtempPinCodeError.innerHTML = "Must be 6 digits.";
        } else {
            IDtempPinCodeError.innerHTML = "";
            tempValid = true;
        }

        // PERMANENT PIN
        if (!IDpermanentPinCode.value) {
            IDpermanentPinCodeError.innerHTML = "This field can't be empty.";
        } else if (IDpermanentPinCode.value.length !== 6) {
            IDpermanentPinCodeError.innerHTML = "Must be 6 digits.";
        } else {
            IDpermanentPinCodeError.innerHTML = "";
            permValid = true;
        }

        // BUTTON CONTROL
        if (tempValid && permValid) {
            $("#nextBtn7")
                .removeClass("disabled")
                .off("click")
                .on("click", function () {
                    saveData("7", "1");
                });
        } else {
            $("#nextBtn7").addClass("disabled").off("click");
        }


    if (!IDpermanentSearchInput.value) {
        IDpermanentSearchInputError.innerHTML = "This field can't be empty.";
    } else {
        IDpermanentSearchInputError.innerHTML = "";
    }

    // if (!IDpermanentLongitude.value) {
    //     IDpermanentLongitudeError.innerHTML = "This field can't be empty.";
    // } else {
    //     IDpermanentLongitudeError.innerHTML = "";
    // }

    // if (!IDpermanentLatitude.value) {
    //     IDpermanentLatitudeError.innerHTML = "This field can't be empty.";
    // } else {
    //     IDpermanentLatitudeError.innerHTML = "";
    // }

    if (!IDtemporarySearchInput.value) {
        IDtemporarySearchInputError.innerHTML = "This field can't be empty.";
    } else {
        IDtemporarySearchInputError.innerHTML = "";
    }

    // if (!IDtemporaryLatitude.value) {
    //     IDtemporaryLatitudeError.innerHTML = "This field can't be empty.";
    // } else {
    //     IDtemporaryLatitudeError.innerHTML = "";
    // }

    // if (!IDtemporaryLongitude.value) {
    //     IDtemporaryLongitudeError.innerHTML = "This field can't be empty.";
    // } else {
    //     IDtemporaryLongitudeError.innerHTML = "";
    // }
}

function updateErrorMessages7() {
    if (!IDstatus.value) {
        IDstatusError.innerHTML = "This field can't be empty";
    } else {
        IDstatusError.innerHTML = "";
    }

    if (!IDcontractType.value) {
        IDcontractTypeError.innerHTML = "This field can't be empty";
    } else {
        IDcontractTypeError.innerHTML = "";
    }

    if (!IDdateOfJoin.value) {
        IDdateOfJoinError.innerHTML = "This field can't be empty";
    } else {
        IDdateOfJoinError.innerHTML = "";
    }

    if (!IDemployeeJobStatus.value) {
        IDemployeeJobStatusError.innerHTML = "This field can't be empty";
    } else {
        IDemployeeJobStatusError.innerHTML = "";
    }
}

function updateErrorMessages5() {
    if (IDbranch.value === "") {
        IDbranchError.innerHTML = "Please select a branch.";
    } else {
        IDbranchError.innerHTML = "";
    }

    if (IDdepartment.value === "") {
        IDdepartmentError.innerHTML = "Please select a department.";
    } else {
        IDdepartmentError.innerHTML = "";
    }

    if (IDdesignation.value === "") {
        IDdesignationError.innerHTML = "Please select a designation.";
    } else {
        IDdesignationError.innerHTML = "";
    }

    if (IDgradeTADA.value === "") {
        IDgradeTADAError.innerHTML = "Please select a grade.";
    } else {
        IDgradeTADAError.innerHTML = "";
    }

    if (IDrole.value === "") {
        IDroleError.innerHTML = "Please select a role.";
    } else {
        IDroleError.innerHTML = "";
    }

    if (IDreportManager.value === "") {
        IDreportManagerError.innerHTML = "Please select a reporting manager.";
    } else {
        IDreportManagerError.innerHTML = "";
    }


}

function updateErrorMessages4() {
    // if (!IDaccountCode.value) {
    //     IDaccountCodeError.innerHTML = "This field can't be empty.";
    // } else {
    //     IDaccountCodeError.innerHTML = "";
    // }
    if (IDbank_account_number.value) {
        if (!IDbank_account_number.value.length < 10) {
            IDbank_account_numberError.innerHTML =
                "Account number must be 10 digits more.";
            $("#nextBtn9").addClass("disabled").off("click");
        } else {
            IDbank_account_numberError.innerHTML = "";
        }
    } else {
        IDbank_account_numberError = "";
    }
}

function handlePfEsicChange() {
    let pfEnabled = IDpf_enable.value;
    let esicEnabled = IDesic_limit.value;

    // Check PF Number if PF is enabled
    if (pfEnabled == 120) {
        document.getElementById('pf_err_span').innerHTML = ' *';
    } else {
        document.getElementById('pf_err_span').innerHTML = '';
    }

    // Check ESIC Number if ESIC is enabled
    if (esicEnabled == 120) {
        document.getElementById('esic_err_span').innerHTML = ' *';
    } else {
        document.getElementById('esic_err_span').innerHTML = '';
    }
}

function updateErrorMessagesstate6(){
     if (!IDemployee_id.value) {
                IDEmpIdError.innerHTML = "This field can't be empty.";

                changeTab(0, state);
            } else {
                IDEmpIdError.innerHTML = "";
            }

            if (!IDattendanceMethod.value) {
                IDattendanceMethodError.innerHTML = "This field can't be empty.";
            } else {
                IDattendanceMethodError.innerHTML = "";
            }

            // if (masterVal.length === 0) {
            //     IDcheckInMethodError.innerHTML = "This field can't be empty.";
            // } else {
            //     IDcheckInMethodError.innerHTML = '';
            // }

            if (!IDattendancePolicy.value) {
                IDattendancePolicyError.innerHTML = "This field can't be empty.";
            } else {
                IDattendancePolicyError.innerHTML = "";
            }

            if (!IDasignShift.value) {
                IDasignShiftError.innerHTML = "This field can't be empty.";
            } else {
                IDasignShiftError.innerHTML = "";
            }

            if (!IDleavePolicy.value) {
                IDleavePolicyError.innerHTML = "This field can't be empty.";
            } else {
                IDleavePolicyError.innerHTML = "";
            }

            if (!IDjoiningLeave.value) {
                IDjoiningLeaveError.innerHTML = "This field can't be empty.";
            } else {
                IDjoiningLeaveError.innerHTML = "";

                if (!IDcalculationMethod.value) {
                    IDcalculationMethodError.innerHTML = "This field can't be empty.";
            } else {
                    IDcalculationMethodError.innerHTML = "";
            }
            }

            if (!IDprobationLeave.value) {
                IDprobationLeaveError.innerHTML = "This field can't be empty.";
            } else {
                IDprobationLeaveError.innerHTML = "";
            }

            if (!IDattendancePreference.value) {
                IDattendancePreferenceError.innerHTML = "This field can't be empty.";
            } else {
                IDattendancePreferenceError.innerHTML = "";
            }

            if (!IDgeofencingId.value) {
                IDgeofencingIdError.innerHTML = "This field can't be empty.";
                } else {
                IDgeofencingIdError.innerHTML = "";
            }

            if (!IDgeoworkId.value) {
                IDgeoworkIdError.innerHTML = "This field can't be empty.";
            } else {
                IDgeoworkIdError.innerHTML = "";
            }

            if (!IDweekOffId.value) {
                IDweekOffIdError.innerHTML = "This field can't be empty.";
            } else {
                IDweekOffIdError.innerHTML = "";
            }
}

// Tab 1 - Basic Employee Information


IDfirstName.addEventListener('input', function() {
    if (this.value.trim()) {
        IDfirstNameErrorShow.innerHTML = "";
    }
});

IDfirstName.addEventListener('blur', function() {
    if (!this.value.trim()) {
        IDfirstNameErrorShow.innerHTML = "This field can't be empty.";
    } else {
        IDfirstNameErrorShow.innerHTML = "";
    }
});

// Emergency Contact
IDemp_emergency_contact.addEventListener('input', function () {
    if (this.value.trim()) {
        IDemp_emergency_contact_error.innerHTML = "";
    }
});

IDemp_emergency_contact.addEventListener('blur', function () {
    if (!this.value.trim()) {
        IDemp_emergency_contact_error.innerHTML = "Emergency Contact number can't be empty.";
    } else {
        IDemp_emergency_contact_error.innerHTML = "";
    }
});



IDifsc.addEventListener('blur', function() {
    if (bankRadio && bankRadio.checked && !this.value.trim()) {
        IDIFSCError.innerHTML = "IFSC Code can't be empty.";
    } else {
        IDIFSCError.innerHTML = "";
    }
});

IDbank_account_number.addEventListener('input', function() {
    if (this.value.trim()) {
        IDbankaccountnumberError.innerHTML = "";
    }
});

IDbank_account_number.addEventListener('blur', function() {
    if (bankRadio && bankRadio.checked) {
        if (!this.value.trim()) {
            IDbankaccountnumberError.innerHTML = "Account No. can't be empty.";
        } else if (this.value.trim().length <= 10) {
            IDbankaccountnumberError.innerHTML = "Account No. must be longer than 10 characters.";
        } else {
            IDbankaccountnumberError.innerHTML = "";
        }
    } else {
        IDbankaccountnumberError.innerHTML = "";
    }
});

IDattendanceMethod.addEventListener('input', function() {
    if (this.value.trim()) {
        IDattendanceMethodError.innerHTML = "";
    }
});



          
function saveData(state, action) {
    activeTab = parseInt(state) + 1;
    if (state == 1) {
        if (
            IDfirstName.value &&
            IDemployee_id.value &&
            IDcontact.value &&
            IDemp_emergency_contact.value &&
            IDemail.value &&
            IDdateOfBirth.value &&
            IDgender.value &&
            // IDmariteStatus.value &&
            // IDbloodGroup.value &&
            IDcontact.value.length == 10 &&
            isValidEmail(IDemail.value) &&
            EmpNotAlreadyExist &&
            EmailNotAlreadyExist &&
            PhoneNotAlreadyExist &&
            IDcontact.value !== IDemp_emergency_contact.value
        ) {
            changeTab(state, 0);
            updateTabs();
        } else {
            if (!IDfirstName.value.trim()) {
                IDfirstNameErrorShow.innerHTML = "This field can't be empty.";
            } else {
                IDfirstNameErrorShow.innerHTML = "";
            }

          
            if (!IDemployee_id.value) {
                IDEmpIdError.innerHTML = "Employee ID can't be empty.";
            } else {
                IDEmpIdError.innerHTML = "";
            }
        

            if (!IDcontact.value) {
                IDcontactError.innerHTML = "Contact number can't be empty.";
            } else {
                IDcontactError.innerHTML = "";
            }


            if (!IDemp_emergency_contact.value) {
                IDemp_emergency_contact_error.innerHTML = "Contact number can't be empty.";
            } else {
                IDemp_emergency_contact_error.innerHTML = "";
            }


            if (IDcontact.value === IDemp_emergency_contact.value && IDcontact.value) {
                IDemp_emergency_contact_error.innerHTML = "Emergency contact number can't be same as personal contact.";
            } else if (!IDemp_emergency_contact.value) {
                IDemp_emergency_contact_error.innerHTML = "Emergency Contact number can't be empty.";
            } else {
                IDemp_emergency_contact_error.innerHTML = "";
            }



            if (!IDemail.value) {
                IDemailError.innerHTML = "Email address can't be empty.";
            } else {
                IDemailError.innerHTML = "";
            }
            if (!isValidEmail(IDemail.value)) {
                IDemailError.innerHTML = "Enter valid email.";
            } else {
                IDemailError.innerHTML = "";
            }

            if (!IDdateOfBirth.value) {
                IDdateOfBirthError.innerHTML = "Date of birth can't be empty.";
            } else {
                IDdateOfBirthError.innerHTML = "";
            }

            if (!IDgender.value) {
                IDgenderError.innerHTML = "Gender can't be empty.";
            } else {
                IDgenderError.innerHTML = "";
            }

            // if (!IDmariteStatus.value) {
            //     IDmariteStatusError.innerHTML =
            //         "Marital status can't be empty.";
            // } else {
            //     IDmariteStatusError.innerHTML = "";
            // }

            // if (!IDbloodGroup.value) {
            //     IDbloodGroupError.innerHTML = "Blood group can't be empty.";
            // } else {
            //     IDbloodGroupError.innerHTML = "";
            // }

            if (IDcontact.value.length != 10) {
                IDcontactError.innerHTML = "Contact number should be 10 digits";
            } else {
                IDcontactError.innerHTML = "";
            }
        }
    } else if (state == 7) {
        if (
            IDstatus.value &&
            IDcontractType.value &&
            IDdateOfJoin.value &&
            IDemployeeJobStatus.value
        ) {
            changeTab(state, 0);
            updateTabs();
        } else {
            updateErrorMessages7();
        }
    }else if (state == 5) {
        if (
            IDbranch.value &&
            IDdepartment.value &&
            IDdesignation.value &&
            IDgradeTADA.value &&
            IDrole.value &&
            IDreportManager.value
        ) {
            changeTab(state, 0);
            updateTabs();
        } else {
            updateErrorMessages5();
        }
    } else if (state == 6) {

        for (var i = 0; i < IDcheckInMethod.length; i++) {
            var masterValue = parseInt(IDcheckInMethod[i].getAttribute("master"), 10);

            if (IDcheckInMethod[i].checked) {
                // Only add the master value if it's not already in the array
                if (!masterVal.includes(masterValue)) {
                    masterVal.push(masterValue);
                }
            }
            else {
                // Remove masterValue if it's in the array (deselected checkbox)
                if (masterVal.includes(masterValue)) {
                    masterVal = masterVal.filter(function (item) {
                        return item !== masterValue;
                    });
                }
            }
        }

        if (IDattendanceMethod.value && (masterVal.length > 0) && IDasignShift.value && IDattendancePolicy.value && IDgeofencingId.value && IDgeoworkId.value && IDweekOffId.value && IDleavePolicy.value && IDjoiningLeave.value && IDprobationLeave.value && IDattendancePreference) {
            if (IDjoiningLeave.value == 1) {
                IDjoiningLeaveError.innerHTML = "";
                if (!IDcalculationMethod.value) {
                    IDcalculationMethodError.innerHTML = "This field can't be empty.";
                    return;
                }
                else if (IDcalculationMethod.value == 366 && !IDapplicableDate.value) {
                    IDapplicableDateError.innerHTML = "This field can't be empty.";
                    return;
                }
                else {
                    IDcalculationMethodError.innerHTML = "";
                    IDapplicableDateError.innerHTML = "";
                }
            }

            changeTab(state, 0);
            updateTabs();
        } else {
           
            updateErrorMessagesstate6();
        }
    } else if (state == 3) {
        changeTab(state, 0);
        updateTabs();
    } else if (state == 9) {


           // Employer Notice Period Validation
        if (!IDemployerNoticePeriod.value) {
            IDemployerNoticePeriodError.innerHTML = "Employer Notice Period can't be empty.";
            return false;
        } else {
            IDemployerNoticePeriodError.innerHTML = "";
        }

       // Employee Notice Period Validation
        if (!IDemployeeNoticePeriod.value) {
            IDemployeeNoticePeriodError.innerHTML = "Employee Notice Period can't be empty.";
            return false;
        } else {
            IDemployeeNoticePeriodError.innerHTML = "";
        }


        changeTab(state, 0);
        updateTabs();
    } else if (state == 2) {
        if (
            IDtempPinCode.value &&
            IDtempPinCode.value.length === 6 &&
            IDpermanentPinCode.value &&
            IDpermanentSearchInput.value &&
            // IDpermanentLongitude.value &&
            // IDpermanentLatitude.value &&
            IDtemporarySearchInput.value
            // IDtemporaryLatitude.value &&
            // IDtemporaryLongitude.value
        ) {
            changeTab(state, 0);
            updateTabs();
        } else {
            updateErrorMessages2();
        }
    } else if (state == 8) {

       let pfEnabled = IDpf_enable.value;
        let esicEnabled = IDesic_limit.value;
        if (pfEnabled == 120) {
            if (!IDPfNumber.value) {
                IDPfNumberError.innerHTML = "PF Number can't be empty.";
                return false;
            } else {
                IDPfNumberError.innerHTML = "";
            }

            if (!IDpfDateOfJoining.value) {
                IDpfDateOfJoiningError.innerHTML = "PF Date of Joining can't be empty.";
                return false;
            } else {
                IDpfDateOfJoiningError.innerHTML = "";
            }
        } else {
            IDPfNumberError.innerHTML = "";
            IDpfDateOfJoiningError.innerHTML = "";
        }

        // ✅ ESIC validation — only when "Yes" (120)
        if (esicEnabled == 120) {
            if (!IDesiNumber.value) {
                IDesiNumberError.innerHTML = "ESIC Number can't be empty.";
                return false;
            } else {
                IDesiNumberError.innerHTML = "";
            }

            if (!IDesiDateOfJoining.value) {
                IDesiDateOfJoiningError.innerHTML = "ESIC Date of Joining can't be empty.";
                return false;
            } else {
                IDesiDateOfJoiningError.innerHTML = "";
            }
        } else {
            IDesiNumberError.innerHTML = "";
            IDesiDateOfJoiningError.innerHTML = "";
        }

        // ✅ Move to next tab no matter what (only if PF or ESIC exists)
        changeTab(state, 0);
        updateTabs();
    } else if (state == 4) {
        let isValid = true;

           if (!bankRadio.checked && !cashRadio.checked && !chequeRadio.checked) {
            paymentMethodError.innerHTML = "Please select a payment method.";
            isValid = false;
        } else {
            paymentMethodError.innerHTML = "";
        }


        if (bankRadio.checked) {
            if (!IDifsc.value.trim()) {
                IDIFSCError.innerHTML = "IFSC Code can't be empty.";
                isValid = false;
            } else {
                IDIFSCError.innerHTML = "";
            }

            if (!IDbank_account_number.value.trim()) {
                IDbankaccountnumberError.innerHTML = "Account No. can't be empty.";
                isValid = false;
            } else if (IDbank_account_number.value.trim().length <= 10) {
                IDbankaccountnumberError.innerHTML = "Account No. must be longer than 10 characters.";
                isValid = false;
            } else {
                IDbankaccountnumberError.innerHTML = "";
            }
        } else {
            IDIFSCError.innerHTML = "";
            IDbankaccountnumberError.innerHTML = "";
        }

        if (isValid) {
            changeTab(state, 0);
            updateTabs();
        } else {
            updateErrorMessages4(); // Make sure this function handles showing errors
        }

    } else if (state == 10) {
        for (var i = 0; i < IDcheckInMethod.length; i++) {
            var masterValue = parseInt(IDcheckInMethod[i].getAttribute("master"), 10);

            if (IDcheckInMethod[i].checked) {
                // Only add the master value if it's not already in the array
                if (!masterVal.includes(masterValue)) {
                    masterVal.push(masterValue);
                }
            }
            else {
                // Remove masterValue if it's in the array (deselected checkbox)
                if (masterVal.includes(masterValue)) {
                    masterVal = masterVal.filter(function (item) {
                        return item !== masterValue;
                    });
                }
            }
        }
        // var file_aadhar = document.getElementById("upload_aadhar").files[0];
        // var file_drivng_license = document.getElementById("upload_drivng_license").files[0];
        // var file_voter_id = document.getElementById("upload_voter_id").files[0];
        // var file_passport = document.getElementById("upload_passport").files[0];
        // var file_passbook = document.getElementById("upload_passbook").files[0];
        // var file_pan = document.getElementById("upload_pan").files[0];
        if (fileInput) {
            var file = fileInput.files[0];
        } else {
            var file = null;
        }
        var formData = new FormData();

        // Handle Aadhaar files
        const aadhaarFiles = document.getElementById("upload_aadhar").files;
        const existingAadhaarFile = document.getElementById(
            "existing_aadhar_file"
        ).value;
        if (aadhaarFiles.length > 0) {
            for (let i = 0; i < aadhaarFiles.length; i++) {
                formData.append("aadharUpload[]", aadhaarFiles[i]);
            }
        } else if (existingAadhaarFile) {
            // formData.append('aadharUpload', document.getElementById('upload_aadhar').getAttribute('data-default-file'));
            formData.append("aadharUpload", existingAadhaarFile);
        }

        // Handle Driving License files
        const drivingLicenseFiles = document.getElementById(
            "upload_drivng_license"
        ).files;
        const existingDrivingLicenseFile = document.getElementById(
            "existing_driving_license_file"
        ).value;
        if (drivingLicenseFiles.length > 0) {
            for (let i = 0; i < drivingLicenseFiles.length; i++) {
                formData.append("drivingUpload[]", drivingLicenseFiles[i]);
            }
        } else if (existingDrivingLicenseFile) {
            formData.append("drivingUpload", existingDrivingLicenseFile);
        }

        // Handle Voter ID files
        const voterIdFiles = document.getElementById("upload_voter_id").files;
        const existingVoterIdFile = document.getElementById(
            "existing_voter_id_file"
        ).value;
        if (voterIdFiles.length > 0) {
            for (let i = 0; i < voterIdFiles.length; i++) {
                formData.append("voterUpload[]", voterIdFiles[i]);
            }
        } else if (existingVoterIdFile) {
            formData.append("voterUpload", existingVoterIdFile);
        }

        // Handle Passport files
        const passportFiles = document.getElementById("upload_passport").files;
        const existingPassportFile = document.getElementById(
            "existing_passport_file"
        ).value;
        if (passportFiles.length > 0) {
            for (let i = 0; i < passportFiles.length; i++) {
                formData.append("passportUpload[]", passportFiles[i]);
            }
        } else if (existingPassportFile) {
            formData.append("passportUpload", existingPassportFile);
        }

        // Handle Passbook files
        const passbookFiles = document.getElementById("upload_passbook").files;
        const existingPassbookFile = document.getElementById(
            "existing_passbook_file"
        ).value;
        if (passbookFiles.length > 0) {
            for (let i = 0; i < passbookFiles.length; i++) {
                formData.append("passbookUpload[]", passbookFiles[i]);
            }
        } else if (existingPassbookFile) {
            formData.append("passbookUpload", existingPassbookFile);
        }

        // Handle PAN files
        const panFiles = document.getElementById("upload_pan").files;
        const existingPanFile =
            document.getElementById("existing_pan_file").value;
        if (panFiles.length > 0) {
            for (let i = 0; i < panFiles.length; i++) {
                formData.append("panUpload[]", panFiles[i]);
            }
        } else if (existingPanFile) {
            formData.append("panUpload", existingPanFile);
        }

        const modalId = 'uploadModal2';
        const croppedFile = window.modalState[modalId]?.finalCroppedFile;
        const selectedGeoBranches = Array.from(IDassigngeobranch.selectedOptions).map(option => option.value);

        formData.append("_token", CSRF);

        formData.append("is_approval_manager", IDemp_approval_manager.value);

        formData.append("emp_primary_id", IDprimary_id.value);
        // formData.append("emp_profile_photo", file);
        formData.append("emp_profile_photo", croppedFile);
        formData.append("emp_code", IDemployee_id.value);
        formData.append("aadhar_number", IDaadhar_number.value);
        formData.append("driving_license_number", IDdrivng_license_number.value);
        formData.append("voter_id_number", IDvoter_id_number.value);
        formData.append("passport_number", passport_number.value);
        formData.append("account_number", IDaccount_number.value);
        formData.append("pan_number", IDpan_number.value);
        // Append basic employee information
        formData.append("prefix", IDprefix.value);
        formData.append("emp_fname", IDfirstName.value);
        formData.append("emp_mname", IDmiddleName.value);
        formData.append("emp_lname", IDlastName.value);
        formData.append("emp_phone", IDcontact.value);
        formData.append("emp_email", IDemail.value);
        formData.append("emp_dob", IDdateOfBirth.value);
        formData.append("emp_gender_id", IDgender.value);
        formData.append("emp_marital_status_id", IDmariteStatus.value);
        formData.append("emp_blood_group_id", IDbloodGroup.value);
        formData.append("emp_status", IDstatus.value);
        formData.append("emp_type_id", IDcontractType.value);
        formData.append("emp_job_status", IDemployeeJobStatus.value);
        formData.append("emp_is_temporary_add_same", tempSameAsParmanant.value);

        // Append dates and other details
        formData.append("emp_date_of_joining", IDdateOfJoin.value);
        formData.append("emp_group_date_of_joining", IDdateOfGroupJoin.value);
        formData.append("emp_date_of_gratuity", IDdateOfGratuity.value);
        formData.append("emp_date_of_transfer", IDdateOfTransfer.value);
        formData.append("emp_date_of_expected_confirmation", IDdateOfExpectedConfirmation.value);
        formData.append("emp_probation_period", IDprobationPeriod.value);
        formData.append("emp_date_of_confirmation", IDdateOfConfirmation.value);
        formData.append("emp_date_of_pay_structure", IDdateOfPayStructure.value);

        // Append ESIC and PF details
        formData.append("emp_esic_limit", IDesic_limit.value);//IDpf_enable
        formData.append("emp_is_pf_enabled", IDpf_enable.value);//
        formData.append("emp_br_id", IDbranch.value);
        formData.append("emp_d_id", IDdepartment.value);
        formData.append("emp_dg_id", IDdesignation.value);
        formData.append("emp_grade_id", IDgradeTADA.value);
        formData.append("emp_role_id", IDrole.value);
        formData.append("emp_supervisor_id", IDreportManager.value);
        formData.append("emp_sap_budget_code", IDbudgetCode.value);
        formData.append("emp_work_mode_id", IDattendanceMethod.value);
        formData.append('emp_checkin_method_id', masterVal);
        formData.append("emp_shift_type_id", IDasignShift.value);
        formData.append("emp_ap_id", IDattendancePolicy.value);
        formData.append("emp_is_geofencing_active", IDgeofencingId.value);
        formData.append("emp_pwo_id", IDweekOffId.value);
        formData.append("emp_attendance_preference", $("input[name='emp_attendance_preference']:checked").val());
        formData.append("emp_is_geowork_active", IDgeoworkId.value);
        formData.append("assign_geo_branch", JSON.stringify(selectedGeoBranches));

        formData.append("emp_pl_id", IDleavePolicy.value);
        formData.append("emp_allow_joining_leave", IDjoiningLeave.value);
        formData.append("emp_joining_leave_calc_type", IDcalculationMethod.value);
        formData.append("emp_joining_leave_before_date", IDapplicableDate.value);
        formData.append("emp_allow_probation_leave", IDprobationLeave.value);
        formData.append("emp_pf_trust_code", IDpfTrustCode.value);
        formData.append("emp_pf_found_member", IDpfFoundMember.value);
        formData.append("emp_pf_no", IDPfNumber.value);
        formData.append("emp_pf_universal_ac_no", IDuniversalAccountNumber.value);
        formData.append("vpfPercentage", IDvpfPercentage.value);
        formData.append("emp_pf_joining_date", IDpfDateOfJoining.value);
        formData.append("emp_pf_leaving_date", IDpfDateOfLeaving.value);
        formData.append("emp_pr_leaving_reason", IDreasonOfLeavingPF.value);
        formData.append("emp_esic_no", IDesiNumber.value);
        formData.append("emp_esic_dispensary", IDesiDespensary.value);
        formData.append("emp_esic_joining_date", IDesiDateOfJoining.value);
        formData.append("emp_esic_leaving_date", IDesiDateOfLeaving.value);
        formData.append("emp_esic_leaving_reason", IDreasonOfLeavingESIC.value);


        formData.append("emp_group_insured_by", IDgroupInsuredBy.value);
        formData.append("emp_group_insurance_no", IDgroupInsuranceNumber.value);
        formData.append("emp_group_insurance_start_date", IDgroupInsuranceStartDate.value);
        formData.append("emp_group_insurance_till_date", IDgroupInsuranceTillDate.value);


        // Append previous organizations
        // formData.append("po_company_name", IDcompanyName.value);
        // formData.append("po_dg_id", IDdesignationName.value);
        // formData.append("po_joining_period", IDjoiningPeriod.value);

        // Assuming you have multiple sets of data, e.g., companyName, designationName, joiningPeriod
        formData.append("po_company_name[]", IDcompanyName.value);
        formData.append("po_dg_id[]", IDdesignationName.value);
        formData.append("po_from_date[]", IDjoiningPeriod.value);
        formData.append("po_to_date[]", IDleavingPeriod.value);
        formData.append("po_serviceduration[]", IDserviceDuration.value);
        formData.append("po_id[]", IDpo.value);

        formData.append("po_company_name[]", IDcompanyName2.value);
        formData.append("po_dg_id[]", IDdesignationName2.value);
        formData.append("po_from_date[]", IDjoiningPeriod2.value);
        formData.append("po_to_date[]", IDleavingPeriod2.value);
        formData.append("po_serviceduration[]", IDserviceDuration2.value);
        formData.append("po_id[]", IDpo2.value);

        // Append qualification details
        formData.append("eq_primary_id", primaryEQID.value);
        formData.append("eq_qualification_id", IDqualification.value);
        formData.append("eq_stream_id", IDstream.value);
        formData.append("eq_course_type_id", IDcource_type.value);
        formData.append("eq_specialization", IDspecalization.value);
        formData.append("eq_course_nature", IDcource_nature.value);
        formData.append("eq_qualification_status", IDquali_status.value);
        formData.append("eq_institution_name", IDinstitute_name.value);
        formData.append("eq_university_name", IDuniversityName.value);
        formData.append("eq_edu_from_date", IDedu_from_date.value);
        formData.append("eq_edu_to_date", IDedu_to_date.value);
        formData.append("eq_passing_date", IDpassing_date.value);
        formData.append("eq_percentage", IDpercentage.value);
        formData.append("eq_edu_grade", IDgrade.value);
        formData.append("eq_duration", IDduration.value);
        formData.append("year", IDyear.value);

        // Map data permanent
        formData.append("emp_permanent_address", IDpermanentSearchInput.value);
        formData.append("emp_permanent_longitude", IDpermanentLongitude.value);
        formData.append("emp_permanent_latitude", IDpermanentLatitude.value);
        // Append permanent address details
        formData.append("emp_permanent_pin_code", IDpermanentPinCode.value);

        // Map data temporary
        formData.append("emp_temporary_address", IDtemporarySearchInput.value);
        formData.append("emp_temporary_longitude", IDtemporaryLongitude.value);
        formData.append("emp_temporary_latitude", IDtemporaryLatitude.value);
        // Append temporary address details
        formData.append("emp_temporary_pin_code", IDtempPinCode.value);

        // Append retirement and separation details
        formData.append("emp_year_of_service", IDyearOfService.value);
        formData.append("emp_retirement_date", IDretirementDate.value);
        formData.append('emp_separation_submit_date', IDseparationSubmitDate.value);
        formData.append("emp_expected_leaving_date", IDexpectedLeavingDate.value);
        formData.append("emp_leaving_date_as_per_notice_period", IDleavingDateAsPerNoticePeriod.value);
        formData.append("emp_notice_period_req_days", IDnoticePeriodRequiredDate.value);
        formData.append("emp_leaving_reason", IDreasonForLeave.value);
        formData.append("emp_leave_date", IDleaveDate.value);
        formData.append("emp_notice_period_serve_days", IDnoticePeriodServedDate.value);
        formData.append("emp_settlement_from_date", IDsettlementFrom.value);
        formData.append("emp_final_settlement_date", IDfinalSettlementDate.value);
        formData.append("noticePeriodShortfallDays", IDnoticePeriodShortfallDays.value);
        formData.append("emp_exit_interview_date", IDexitInterviewDate.value);
        formData.append("emp_last_working_date", IDlastWorkingDate.value);
        formData.append("emp_remark", IDremark.value);
        formData.append("employerNoticePeriod", IDemployerNoticePeriod.value);
        formData.append("employeeNoticePeriod", IDemployeeNoticePeriod.value);

        // Append bank details
        formData.append("emp_account_code", IDaccountCode.value);
        formData.append("emp_bank_ifsc_code", IDifsc.value);
        formData.append("emp_bank_name", IDbankName.value);
        formData.append("emp_bank_branch_name", IDbranchName.value);
        formData.append("emp_bank_account_no", IDbank_account_number.value);
        formData.append("emp_bank_micr_code", IDMICR.value);
        formData.append("emp_bank_branch_code", IDbranchCode.value);

        // Append Salary bank details
        formData.append("emp_salary_account_code", IDemp_salary_accountCode.value);
        formData.append("emp_salary_bank_ifsc_code", IDemp_salary_ifsc.value);
        formData.append("emp_salary_bank_name", IDemp_salary_bankName.value);
        formData.append("emp_salary_bank_branch_name", IDemp_salary_branchName.value);
        formData.append("emp_salary_bank_micr_code", IDemp_salary_micr.value);
        formData.append("emp_salary_bank_branch_code", IDemp_salary_branch_code.value);
        formData.append("emp_salary_bank_account_no", IDemp_salary_bank_account_number.value);

        // Add some new input fileds 
        formData.append("emp_official_contact", IDemp_official_contact.value);
        formData.append("emp_official_email", IDemp_official_email.value);
        formData.append("emp_emergency_relation", IDemp_emergency_relation.value);
        formData.append("emp_emergency_contact", IDemp_emergency_contact.value);

        // Optional Info
        formData.append("emp_nationality", IDemp_nationality.value);
        formData.append("emp_religion", IDemp_religion.value);
        formData.append("emp_category", IDemp_category.value);
        formData.append("emp_body_mark", IDemp_body_mark.value);

        // Bank Details - Salary Account
        formData.append("emp_salary_accountCode", IDemp_salary_accountCode.value);
        formData.append("emp_salary_ifsc", IDemp_salary_ifsc.value);
        formData.append("emp_salary_bankName", IDemp_salary_bankName.value);
        formData.append("emp_salary_branchName", IDemp_salary_branchName.value);
        formData.append("emp_salary_micr", IDemp_salary_micr.value);
        formData.append("emp_salary_branch_code", IDemp_salary_branch_code.value);
        formData.append("emp_salary_bank_account_number", IDemp_salary_bank_account_number.value);


        // Offline Sync
        formData.append("emp_offline_status", IDemp_offline_status.value);
         
        // Organization Information
        formData.append("profitCenter", IDprofitCenter.value);
        formData.append("costCenter", IDcostCenter.value);
        formData.append("assignedRegion", IDassignedRegion.value);


        
        // Employee Notice Period
        formData.append("employee_notice_period", IDemployeeNoticePeriod.value);

        // Employer Notice Period
        formData.append("employer_notice_period", IDemployerNoticePeriod.value);

        

        // Joining
        formData.append("emp_project_assigned", IDemp_project_assigned.value);

        // EPS Enabale
        formData.append("emp_is_eps_enabled", IDEmpEpsEnalbed.value);

        // Valid Thru
        formData.append("emp_passport_valid", IDEmpPassport.value);
        formData.append("emp_drivng_license_valid", IDEmpdrivinglicense.value);

        // Account Purspose 
        formData.append("account_type", Idaccountyype.value);


        
        // Document Uplode
        if (IDEmpdocaadhar.files[0]) {
            formData.append("upload_aadhar", IDEmpdocaadhar.files[0]);
        }
        if (IDEmpdocdriving.files[0]) {
            formData.append("upload_drivng_license", IDEmpdocdriving.files[0]);
        }
        if (IDEmpdocvoter.files[0]) {
            formData.append("upload_voter_id", IDEmpdocvoter.files[0]);
        }
        if (IDEmpdocpassbook.files[0]) {
            formData.append("upload_passbook", IDEmpdocpassbook.files[0]);
        }
        if (IDEmpdocpassport.files[0]) {
            formData.append("upload_passport", IDEmpdocpassport.files[0]);
        }
        if (IDEmpdocpan.files[0]) {
            formData.append("upload_pan", IDEmpdocpan.files[0]);
        }


        // Check which radio button is selected and append the value
        if (bankRadio.checked) {
            formData.append("payment", bankRadio.value);
        } else if (cashRadio.checked) {
            formData.append("payment", cashRadio.value);
        } else if (chequeRadio.checked) {
            formData.append("payment", chequeRadio.value);
        } else {
            alert("Please select a payment method.");
        }


        var saveButton = document.getElementById("save-button");
        // $('save-button').
        saveButton.disabled = true;
        saveButton.innerText = "Saving...";

        // $.ajax({
        //     url: formSubmitURL,
        //     type: "POST",
        //     data: formData,
        //     processData: false, // Important: Do not process data
        //     contentType: false, // Important: Set content type to false
        //     cache: false, // Prevent caching
        //     dataType: "json",
        //     success: function (res) {
        //         if (!res.status) {
        //             Swal.fire({
        //                 icon: "error",
        //                 text: "Error in saving data: " + res.message,
        //                 timer: 3000,
        //             });
        //             saveButton.disabled = false;
        //             saveButton.innerText = "Save & Finish";
        //         } else {
        //             if (res.redirect) {
        //                 window.location.href = res.redirect;
        //                 return;
        //             }
        //             Swal.fire({
        //                 icon: "success",
        //                 text: res.message || "Data saved successfully.",
        //                 timer: 1500,
        //                 showConfirmButton: false
        //             }).then(() => {
        //                 // Only redirect if emp_id is present (i.e., create case)
        //                 if (res.emp_id) {
        //                     let routeTemplate = window.routes.employeeAddEditPayroll;
        //                     const redirectUrl = routeTemplate.replace('REPLACE_ID', res.emp_id);
        //                     window.location.href = redirectUrl;
        //                 }
        //                 // Else, no redirect (edit case)

                        
        //             });

        //             formCompleted = true;
        //             updateTabs();
        //             window.location.href = redirectURL;
        //         }
        //     },
        //     error: function (jqXHR, textStatus, errorThrown) {
        //         console.log("AJAX request failed:", textStatus, errorThrown);
        //         saveButton.disabled = false;
        //         saveButton.innerText = "Save & Finish";
        //         if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
        //             // Create a list of error messages
        //             let errorMessages = '';
        //             for (let field in jqXHR.responseJSON.errors) {
        //                 errorMessages += jqXHR.responseJSON.errors[field].join(', ') + '\n'; // Join messages for each field
        //             }

        //             // Show SweetAlert with validation errors
        //             Swal.fire({
        //                 icon: 'error', // Icon type (error, warning, info, success)
        //                 title: 'Validation Errors',
        //                 text: errorMessages, // Display the error messages
        //                 // footer: `<a href="">Why do I have this issue?</a>` // Optional footer link
        //             });
        //         } else {
        //             // Handle other types of errors
        //             Swal.fire({
        //                 icon: 'error',
        //                 title: 'Oops...',
        //                 text: 'something went wrong!', // Custom message for generic errors
        //                 // footer: `<a href="">Why do I have this issue?</a>` // Optional footer link
        //             });
        //         }
        //     },
        // });
        // formCompleted = true;

          $.ajax({
            url: formSubmitURL,
            method: "POST",                    // ← changed from type (recommended since jQuery 1.9+)
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            dataType: "json",
            timeout: 45000,                    // ← added: prevents hanging forever on bad connection
            success: function (res) {
                if (!res.status) {
                    Swal.fire({
                        icon: "error",
                        text: "Error in saving data: " + (res.message || "Unknown error"),
                        timer: 3000,
                    });
                    saveButton.disabled = false;
                    saveButton.innerText = "Save & Finish";
                    return;
                }

                Swal.fire({
                icon: "success",
                title: "Success",
                text: res.message || "Data saved successfully.",
                showCancelButton: true,
                confirmButtonText: "Close",
                cancelButtonText: "Update Salary Master",
                confirmButtonColor: "#ff2600", 
                cancelButtonColor: "#3085d6"

            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.assign("/employees");
                }

                if (result.dismiss === Swal.DismissReason.cancel) {
                    if (res.redirect) {
                        window.location.assign(res.redirect);
                    }
                }
            });


                formCompleted = true;
                updateTabs();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log("AJAX request failed:", textStatus, errorThrown);

                const response = jqXHR.responseJSON || {};

                if (jqXHR.status === 403 && response.type === 'LIMIT_EXCEEDED') {
                    Swal.fire({
                        icon: "warning",
                        title: "Employee Limit Reached",
                        text: response.message || "Maximum number of employees reached.",
                        confirmButtonText: "Upgrade Plan"
                    });
                }
                else if (response.errors) {
                    // Better readable error list
                    let errorMessages = '';
                    for (let field in response.errors) {
                        errorMessages += response.errors[field].join(', ') + '\n';
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Errors',
                        text: errorMessages.trim() || 'Please check the form fields.',
                    });
                }
                else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message || 'Something went wrong! Please try again.',
                    });
                }

                saveButton.disabled = false;
                saveButton.innerText = "Save & Finish";
            }
        });
    }

    if (!EmpNotAlreadyExist) {
        checkEmployeeId(IDemployee_id);
    }

    if (!PhoneNotAlreadyExist) {
        checkPhoneEmail(IDcontact, 0);
    }

    if (!EmailNotAlreadyExist) {
        checkPhoneEmail(IDemail, 1);
    }
}

function previousData(state, action) {
    activeTab = parseInt(state) - 1;
    updateTabs();
    if (state == 2) {
        tab1s.classList.remove("d-none");
        tabBtn1.classList.remove("d-none");
        tabCard1.classList.remove("d-none");
        tab2s.classList.add("d-none");
        tabBtn2.classList.add("d-none");
        tabCard2.classList.add("d-none");
    } else if (state == 3) {
        tab2s.classList.remove("d-none");
        tabBtn2.classList.remove("d-none");
        tabCard2.classList.remove("d-none");
        tab3s.classList.add("d-none");
        tabBtn3.classList.add("d-none");
        tabCard3.classList.add("d-none");
    } else if (state == 4) {
        tab3s.classList.remove("d-none");
        tabBtn3.classList.remove("d-none");
        tabCard3.classList.remove("d-none");
        tab4s.classList.add("d-none");
        tabBtn4.classList.add("d-none");
        tabCard4.classList.add("d-none");
    } else if (state == 5) {
        tab4s.classList.remove("d-none");
        tabBtn4.classList.remove("d-none");
        tabCard4.classList.remove("d-none");
        tab5s.classList.add("d-none");
        tabBtn5.classList.add("d-none");
        tabCard5.classList.add("d-none");
    } else if (state == 6) {
        tab5s.classList.remove("d-none");
        tabBtn5.classList.remove("d-none");
        tabCard5.classList.remove("d-none");
        tab6s.classList.add("d-none");
        tabBtn6.classList.add("d-none");
        tabCard6.classList.add("d-none");
    } else if (state == 7) {
        tab6s.classList.remove("d-none");
        tabBtn6.classList.remove("d-none");
        tabCard6.classList.remove("d-none");
        tab7s.classList.add("d-none");
        tabBtn7.classList.add("d-none");
        tabCard7.classList.add("d-none");
    } else if (state == 8) {
        tab7s.classList.remove("d-none");
        tabBtn7.classList.remove("d-none");
        tabCard7.classList.remove("d-none");
        tab8s.classList.add("d-none");
        tabBtn8.classList.add("d-none");
        tabCard8.classList.add("d-none");
    } else if (state == 9) {
        tab8s.classList.remove("d-none");
        tabBtn8.classList.remove("d-none");
        tabCard8.classList.remove("d-none");
        tab9s.classList.add("d-none");
        tabBtn9.classList.add("d-none");
        tabCard9.classList.add("d-none");
    } else if (state == 9) {
        tab9s.classList.remove("d-none");
        tabBtn9.classList.remove("d-none");
        tabCard9.classList.remove("d-none");
        AvtarDiv.classList.remove("d-none");
        tab10s.classList.add("d-none");
        tabBtn10.classList.add("d-none");
    } else if (state == 10) {
        tab9s.classList.remove("d-none");
        tabBtn9.classList.remove("d-none");
        tabCard9.classList.remove("d-none");
        AvtarDiv.classList.remove("d-none");
        tab10s.classList.add("d-none");
        tabBtn10.classList.add("d-none");
    }
}

function updateTabs() {
    for (let i = 1; i <= 10; i++) {
        const tab = document.getElementById(`tab-${i}`);
        const icon = document.getElementById(`icon-${i}`);
        if (i < activeTab) {
            tab.classList.add("text-dark");
            tab.classList.remove("text-muted");
            icon.className = "ion-checkmark-circled";
        } else if (i === activeTab) {
            tab.classList.add("text-primary");
            tab.classList.remove("text-dark", "text-muted");
            icon.className = "fa fa-circle";
        } else {
            tab.classList.add("text-muted");
            tab.classList.remove("text-primary", "text-dark");
            icon.className = "fa fa-circle";
        }
    }
}

// function updateProgressBar() {dat
//     const progressBar = document.getElementById('progressBar');
//     const percentage = (activeTab / 10) * 100;
//     progressBar.style.width = `${percentage}%`;
//     progressBar.setAttribute('aria-valuenow', percentage);
// }

document.addEventListener("DOMContentLoaded", () => {
    updateTabs();
    // updateProgressBar();
});

function checkEmployeeId(e) {
    $.ajax({
        url: empCheckUrl,
        type: "POST",
        data: {
            _token: CSRF,
            emp_id: e.value,
        },
        dataType: "json",
        cache: true,
        success: function (res) {
            if (res) {
                EmpNotAlreadyExist = res.status;
                if (res.status) {
                    IDEmpIdError.style.color = "green";
                } else {
                    IDEmpIdError.style.color = "red";
                }
                IDEmpIdError.innerHTML = res.message;
            }
        },
    });
}

function checkPhoneEmail(e, For) {
    var primary_emp_id = $(e).data("primary-emp-id");
    $("#nextBtn").addClass("disabled").off("click");
    $.ajax({
        url: phoneEmailCheckURL,
        type: "POST",
        data: {
            _token: CSRF,
            primary_emp_id: primary_emp_id,
            id: e.value,
            for: For,
        },
        dataType: "json",
        cache: true,
        success: function (res) {
            if (For == 0) {
                PhoneNotAlreadyExist = res.status;
                if (res.status) {
                    IDcontactError.style.color = "green";
                    $("#nextBtn").removeClass("disabled")
                        .on("click", function () {
                            saveData("1", "1");
                        });
                } else {
                    IDcontactError.style.color = "red";
                }
                IDcontactError.innerHTML = res.message;
            } else if (For == 1) {
                EmailNotAlreadyExist = res.status;
                if (res.status) {
                    IDemailError.style.color = "green";
                    $("#nextBtn").removeClass("disabled")
                        .on("click", function () {
                            saveData("1", "1");
                        });
                } else {
                    IDemailError.style.color = "red";
                }
                IDemailError.innerHTML = res.message;
            }
        },
    });
}
var elem = 2;

function isValidEmail(email) {
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function changeTab(action, current) {
    if (action == 0) {
        var current = "tab" + current + "s";
        var current1 = "tabBtn" + current;
        var goOn = "tab" + action + "s";
        var goOn1 = "tabBtn" + action;

        current.classList.add("d-none");
        current1.classList.add("d-none");
        goOn.classList.remove("d-none");
        goOn1.classList.remove("d-none");
    } else if (action == 1) {
        tab1s.classList.add("d-none");
        tabBtn1.classList.add("d-none");
        tabCard1.classList.add("d-none");
        tab2s.classList.remove("d-none");
        tabBtn2.classList.remove("d-none");
        tabCard2.classList.remove("d-none");
    } else if (action == 2) {
        tab2s.classList.add("d-none");
        tabBtn2.classList.add("d-none");
        tabCard2.classList.add("d-none");
        tab3s.classList.remove("d-none");
        tabBtn3.classList.remove("d-none");
        tabCard3.classList.remove("d-none");
    } else if (action == 3) {
        tab3s.classList.add("d-none");
        tabBtn3.classList.add("d-none");
        tabCard3.classList.add("d-none");
        tab4s.classList.remove("d-none");
        tabBtn4.classList.remove("d-none");
        tabCard4.classList.remove("d-none");
    } else if (action == 4) {
        tab4s.classList.add("d-none");
        tabBtn4.classList.add("d-none");
        tabCard4.classList.add("d-none");
        tab5s.classList.remove("d-none");
        tabBtn5.classList.remove("d-none");
        tabCard5.classList.remove("d-none");
    } else if (action == 5) {
        tab5s.classList.add("d-none");
        tabBtn5.classList.add("d-none");
        tabCard5.classList.add("d-none");
        tab6s.classList.remove("d-none");
        tabBtn6.classList.remove("d-none");
        tabCard6.classList.remove("d-none");
    } else if (action == 6) {
        tab6s.classList.add("d-none");
        tabBtn6.classList.add("d-none");
        tabCard6.classList.add("d-none");
        tab7s.classList.remove("d-none");
        tabBtn7.classList.remove("d-none");
        tabCard7.classList.remove("d-none");
    } else if (action == 7) {
        tab7s.classList.add("d-none");
        tabBtn7.classList.add("d-none");
        tabCard7.classList.add("d-none");
        tab8s.classList.remove("d-none");
        tabBtn8.classList.remove("d-none");
        tabCard8.classList.remove("d-none");
    } else if (action == 8) {
        tab8s.classList.add("d-none");
        tabBtn8.classList.add("d-none");
        tabCard8.classList.add("d-none");
        tab9s.classList.remove("d-none");
        tabBtn9.classList.remove("d-none");
        tabCard9.classList.remove("d-none");
    } else if (action == 9) {
        tab9s.classList.add("d-none");
        tabBtn9.classList.add("d-none");
        tabCard9.classList.add("d-none");
        AvtarDiv.classList.add("d-none");
        tab10s.classList.remove("d-none");
        tabBtn10.classList.remove("d-none");
        fetchData();
    } else if (action == 10) {
        tab10s.classList.add("d-none");
        tabBtn10.classList.add("d-none");
    }
}

// function checkIFSC(ifsc) {
//     $.ajax({
//         url: ifscfURL,
//         type: "POST",
//         data: {
//             _token: CSRF,
//             ifsc: ifsc,
//         },
//         dataType: "json",
//         cache: true,
//         success: function(res) {
//             if (res.status) {
//                 $('#bankName').val(res.data.BANK);
//                 $('#branchName').val(res.data.BRANCH + ", " + res.data.ADDRESS);
//                 $('#micr').val(res.data.MICR);
//                 $('#branch_code').val(res.data.BANKCODE);

//                 // Optional: also show read-only text somewhere if needed
//                 $('#bankNameElement').text(res.data.BANK);
//                 $('#branchNameElement').text(res.data.BRANCH);
//                 $('#micrElement').text(res.data.MICR);
//                 $('#bankCodeElement').text(res.data.BANKCODE);
//             } else {
//                 alert("Invalid IFSC Code or no data found.");
//             }
//         },
//         error: function() {
//             alert("Something went wrong while fetching IFSC details.");
//         }
//     });
// }



function checkIFSC(ifsc) {
    $.ajax({
        url: ifscfURL,
        type: "POST",
        data: {
            _token: CSRF,
            ifsc: ifsc,
        },
        dataType: "json",
        cache: true,
        success: function (res) {
            if (res.status) {
                IDbankName.value = res.data.BANK;
                IDbranchName.value = res.data.BRANCH + ", " + res.data.ADDRESS;
                IDMICR.value = res.data.MICR;
                IDbranchCode.value = res.data.BANKCODE;
                bankNameElement.innerHTML = res.data.BANK;
                branchNameElement.innerHTML = res.data.BRANCH;
                micrElement.innerHTML = res.data.MICR;
                bankCodeElement.innerHTML = res.data.BANKCODE;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid IFSC Code',
                    text: 'Invalid IFSC Code or no data found.',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong while fetching IFSC details.',
                confirmButtonText: 'OK'
            });
        }
    });
}


function salaryifsc(ifsc) {
    $.ajax({
        url: salaryifscfURL,
        type: "POST",
        data: {
            _token: CSRF,
            emp_salary_ifsc: ifsc,
        },
        dataType: "json",
        cache: true,
        success: function (res) {
            if (res.status) {
                $('#emp_salary_bankName').val(res.data.BANK);
                $('#emp_salary_branchName').val(res.data.BRANCH + ", " + res.data.ADDRESS);
                $('#emp_salary_micr').val(res.data.MICR);
                $('#emp_salary_branch_code').val(res.data.BANKCODE);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid IFSC Code',
                    text: 'Invalid IFSC Code or no data found.',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong while fetching IFSC details.',
                confirmButtonText: 'OK'
            });
        }
    });
}

function safeAssign(element, value, defaultValue = "---") {
    element.innerHTML =
        value == null || value == undefined || value == 0 || value == ""
            ? defaultValue
            : value;
}

function handleChange(event) {
    if (event.target.id) {
        if (event.target.id == "email") {
            EmailText.innerHTML = event.target.value;
        }
    }

    var IDattendancePreference = document.querySelector('input[name="emp_attendance_preference"]:checked');

    // Check if a radio button is selected
    if (IDattendancePreference) {
        const labelText = IDattendancePreference.nextElementSibling ? IDattendancePreference.nextElementSibling.textContent.trim() : null;
        safeAssign(attendancePreferenceText, labelText);
    }

    // IDprefix.options[IDprefix.selectedIndex].text
    safeAssign(bankCodeElement, IDbranchCode.value);
    safeAssign(branchNameElement, IDbranchName.value);
    safeAssign(micrElement, IDMICR.value);
    safeAssign(empIDText, IDemployee_id.value);
    safeAssign(contactText, IDcontact.value);
    // safeAssign(birthdayText, IDdateOfBirth.value);

    const d = new Date(IDdateOfBirth.value);
    const formattedDate = `${d.getDate().toString().padStart(2, '0')}-${["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"][d.getMonth()]}-${d.getFullYear()}`;
    safeAssign(birthdayText, formattedDate);
    safeAssign(genderText, IDgender.options[IDgender.selectedIndex].text);
    safeAssign(
        martialText,
        IDmariteStatus.options[IDmariteStatus.selectedIndex].text
    );
    safeAssign(
        bloodGroupText,
        IDbloodGroup.options[IDbloodGroup.selectedIndex].text
    );
    safeAssign(activeText, IDstatus.options[IDstatus.selectedIndex].text);
    safeAssign(
        contractText,
        IDcontractType.options[IDcontractType.selectedIndex].text
    );
    safeAssign(
        jobStatusText,
        IDemployeeJobStatus.options[IDemployeeJobStatus.selectedIndex].text
    );
    // safeAssign(joiningDateText, IDdateOfJoin.value);
    // safeAssign(groupJoiningText, IDdateOfGroupJoin.value);
    // safeAssign(gratuityDateText, IDdateOfGratuity.value);
    // safeAssign(transferDateText, IDdateOfTransfer.value);
    // safeAssign(expectedConfirmationText, IDdateOfExpectedConfirmation.value);
    // safeAssign(probationDateText, IDprobationPeriod.value);
    // safeAssign(confirmationDateText, IDdateOfConfirmation.value);
    // safeAssign(payStructureDateText, IDdateOfPayStructure.value);


        const formatDate = val => {
    if (!val) return '';
    const d = new Date(val);
    if (isNaN(d)) return ''; // handle invalid dates
    return `${String(d.getDate()).padStart(2, '0')}-${["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"][d.getMonth()]}-${d.getFullYear()}`;
    };
    
// Previous assignments
safeAssign(joiningDateText, formatDate(IDdateOfJoin.value));
safeAssign(groupJoiningText, formatDate(IDdateOfGroupJoin.value));
safeAssign(gratuityDateText, formatDate(IDdateOfGratuity.value));
safeAssign(transferDateText, formatDate(IDdateOfTransfer.value));
safeAssign(expectedConfirmationText, formatDate(IDdateOfExpectedConfirmation.value));
safeAssign(probationDateText, IDprobationPeriod.value);
safeAssign(confirmationDateText, formatDate(IDdateOfConfirmation.value));
safeAssign(payStructureDateText, formatDate(IDdateOfPayStructure.value));

// New assignments
safeAssign(pfJoinDate, formatDate(IDpfDateOfJoining.value));
safeAssign(pfLeaveDate, formatDate(IDpfDateOfLeaving.value));
safeAssign(esicDateOfJoining, formatDate(IDesiDateOfJoining.value));
safeAssign(esicDateOfLeave, formatDate(IDesiDateOfLeaving.value));
safeAssign(fromDate, formatDate(IDedu_from_date.value));
safeAssign(toDate, formatDate(IDedu_to_date.value));
safeAssign(passingDate, formatDate(IDpassing_date.value));

safeAssign(retirementDateElement, formatDate(IDretirementDate.value));
safeAssign(separationSubmitOnElement, formatDate(IDseparationSubmitDate.value));
safeAssign(expectedLeavingDateElement, formatDate(IDexpectedLeavingDate.value));
safeAssign(leavingDatePeriodElement, formatDate(IDleavingDateAsPerNoticePeriod.value));
safeAssign(noticePeriodDaysElement, formatDate(IDnoticePeriodRequiredDate.value));

safeAssign(leavingDateElement, formatDate(IDleaveDate.value));
safeAssign(noticeServedDaysElement, formatDate(IDnoticePeriodServedDate.value));

safeAssign(finalSettlementDateElement, formatDate(IDfinalSettlementDate.value));
safeAssign(exitInterviewDateElement, formatDate(IDexitInterviewDate.value));
safeAssign(lastWorkingDateElement, formatDate(IDlastWorkingDate.value));



    
    safeAssign(
        esicLimitText,
        IDesic_limit.options[IDesic_limit.selectedIndex].text
    );
    safeAssign(
        pfLimitText,
        IDpf_enable.options[IDpf_enable.selectedIndex].text
    );

    safeAssign(branchText, IDbranch.options[IDbranch.selectedIndex].text);
    safeAssign(
        departmentText,
        IDdepartment.options[IDdepartment.selectedIndex].text
    );
    safeAssign(roleText, IDrole.options[IDrole.selectedIndex].text);
    safeAssign(
        reportManagerText,
        IDreportManager.options[IDreportManager.selectedIndex].text
    );
    safeAssign(gradeText, IDgradeTADA.options[IDgradeTADA.selectedIndex].text);
    safeAssign(
        designationText,
        IDdesignation.options[IDdesignation.selectedIndex].text
    );
    safeAssign(
        assignMethodText,
        IDattendanceMethod.options[IDattendanceMethod.selectedIndex].text
    );
    safeAssign(
        checkInMethodText, masterVal);
    safeAssign(
        shiftPolicyText,
        IDasignShift.options[IDasignShift.selectedIndex].text
    );
    safeAssign(attendancePolicyText, IDattendancePolicy.options[IDattendancePolicy.selectedIndex].text);
    safeAssign(geofencingText, IDgeofencingId.options[IDgeofencingId.selectedIndex].text);
    safeAssign(geoWorkText, IDgeoworkId.options[IDgeoworkId.selectedIndex].text);
    safeAssign(weekOffText, IDweekOffId.options[IDweekOffId.selectedIndex].text);
    safeAssign(leavePolicyText, IDleavePolicy.options[IDleavePolicy.selectedIndex].text);
    safeAssign(joiningLeaveText, IDjoiningLeave.options[IDjoiningLeave.selectedIndex].text);
    safeAssign(calculationMethodText, IDcalculationMethod.options[IDcalculationMethod.selectedIndex].text);
    safeAssign(applicableDateText, (IDapplicableDate.value).slice(0, 3));
    safeAssign(probationLeaveText, IDprobationLeave.options[IDprobationLeave.selectedIndex].text);
    safeAssign(aadhardNumberText, IDaadhar_number.value);
    safeAssign(budgetCodeText, IDbudgetCode.value);
    // safeAssign(companyNameText, IDcompanyName.value);
    // safeAssign(designationNameText, IDdesignationName.value);
    // safeAssign(joiningPeriodText, IDjoiningPeriod.value);
    safeAssign(drivingLicenseText, IDdrivng_license_number.value);
    safeAssign(electionCardText, IDvoter_id_number.value);
    safeAssign(passportText, IDpassport_number.value);
    safeAssign(bankAcNumText, IDaccount_number.value);
    safeAssign(panNumText, IDpan_number.value);
    safeAssign(pftrustCode, IDpfTrustCode.value);
    safeAssign(pensionMember, IDpfFoundMember.value);
    safeAssign(pfNum, IDPfNumber.value);
    safeAssign(uniAcNum, IDuniversalAccountNumber.value);
    safeAssign(vpfPerc, IDvpfPercentage.value);
    // safeAssign(pfJoinDate, IDpfDateOfJoining.value);
    // safeAssign(pfLeaveDate, IDpfDateOfLeaving.value);
    safeAssign(PfLeaveReason, IDreasonOfLeavingPF.value);
    safeAssign(esicNum, IDesiNumber.value);
    safeAssign(esiDesperacy, IDesiDespensary.value);
    // safeAssign(esicDateOfJoining, IDesiDateOfJoining.value);
    // safeAssign(esicDateOfLeave, IDesiDateOfLeaving.value);
    safeAssign(esicLeaveReason, IDreasonOfLeavingESIC.value);
    // safeAssign(primaryEqId, primaryEQID.value);
    // safeAssign(qualification, IDqualification.value);
    safeAssign(stream, IDstream.options[IDstream.selectedIndex].text);
    safeAssign(
        qualification,
        IDqualification.options[IDqualification.selectedIndex].text
    );
    safeAssign(
        courseType,
        IDcource_type.options[IDcource_type.selectedIndex].text
    );
    safeAssign(specialization, IDspecalization.value);
    safeAssign(
        natureOfCourse,
        IDcource_nature.options[IDcource_nature.selectedIndex].text
    );
    safeAssign(
        qualificationStatus,
        IDquali_status.options[IDquali_status.selectedIndex].text
    );
    safeAssign(instituteName, IDinstitute_name.value);
    safeAssign(universityName, IDuniversityName.value);
    // safeAssign(fromDate, IDedu_from_date.value);
    // safeAssign(toDate, IDedu_to_date.value);
    // safeAssign(passingDate, IDpassing_date.value);
    safeAssign(percentage, IDpercentage.value);
    safeAssign(grade, IDgrade.value);
    safeAssign(durationOfCourse, IDduration.value);
    safeAssign(year, IDyear.value);
    safeAssign(yearOfServiceElement, IDyearOfService.value);
    // safeAssign(retirementDateElement, IDretirementDate.value);
    // safeAssign(separationSubmitOnElement, IDseparationSubmitDate.value);/
    // safeAssign(expectedLeavingDateElement, IDexpectedLeavingDate.value);
    // safeAssign(leavingDatePeriodElement, IDleavingDateAsPerNoticePeriod.value);
    // safeAssign(noticePeriodDaysElement, IDnoticePeriodRequiredDate.value);
    safeAssign(reasonForLeavingElement, IDreasonForLeave.value);
    // safeAssign(leavingDateElement, IDleaveDate.value);
    // safeAssign(noticeServedDaysElement, IDnoticePeriodServedDate.value);
    safeAssign(settlementFromElement, IDsettlementFrom.value);
    // safeAssign(finalSettlementDateElement, IDfinalSettlementDate.value);
    safeAssign(noticePeriodShortfallDaysElement, IDnoticePeriodShortfallDays.value);
    // safeAssign(exitInterviewDateElement, IDexitInterviewDate.value);
    // safeAssign(lastWorkingDateElement, IDlastWorkingDate.value);
    safeAssign(remarkElement, IDremark.value);
    safeAssign(noticePeriodForEmployerElement, IDemployerNoticePeriod.value);
    safeAssign(noticePeriodForEmployeeElement, IDemployeeNoticePeriod.value);
    safeAssign(ifscElement, IDifsc.value);
    safeAssign(accountCodeText, IDaccountCode.value);
    safeAssign(bankNameElement, IDbankName.value);
    safeAssign(bankAccountNumberElement, IDbank_account_number.value);


    // Permanent Address Assignments
    safeAssign(permanentAddressElement, IDpermanentSearchInput.value);
    safeAssign(permanentLongitudeElement, IDpermanentLongitude.value);
    safeAssign(permanentLatitudeElement, IDpermanentLatitude.value);
    safeAssign(permanentPinCodeElement, IDpermanentPinCode.value);

    // Temporary Address Assignments
    safeAssign(temporaryAddressElement, IDtemporarySearchInput.value);
    safeAssign(temporaryLongitudeElement, IDtemporaryLongitude.value);
    safeAssign(temporaryLatitudeElement, IDtemporaryLatitude.value);
    safeAssign(temporaryPinCodeElement, IDtempPinCode.value);


}

function getReportingManagers(event) {
    document.querySelector('#thirdNextBtn').disabled = true;

    let departmentId = event.target.value;

    safeAssign(
        departmentText,
        IDdepartment.options[IDdepartment.selectedIndex].text
    );
    // Call to your server to get the updated list of reporting managers based on the department
    $.ajax({
        url: '/admin/employee/get-employee-data', // Replace with your route to get reporting managers
        method: 'GET',
        data: {
            REQUEST_TYPE: 'get-reporting-managers',
            department_id: departmentId
        },
        success: function (response) {
            // Clear the existing options in the reporting manager dropdown
            $('#reporting_manager').empty();

            // Add the default option
            $('#reporting_manager').append('<option value="">Select Reporting Manager</option>');
            document.getElementById('thirdNextBtn').disabled = false;

            // Populate the reporting manager dropdown with the data from the server
            response.managers.forEach(function (manager) {
                $('#reporting_manager').append(
                    `<option value="${manager.emp_id}">(${manager.emp_code}) ${manager.emp_full_name}</option>`
                );
            });

            // Re-initialize the Select2 plugin after adding new options
            $('#reporting_manager').trigger('change');
        },
        error: function (error) {
            console.log('Error fetching reporting managers:', error);
        }
    });
}
IDfirstName.addEventListener("input", handleChange);
IDmiddleName.addEventListener("change", handleChange);
IDlastName.addEventListener("change", handleChange);
IDemail.addEventListener("change", handleChange);

IDemployee_id.addEventListener("input", handleChange);
IDcontact.addEventListener("change", handleChange);
IDdateOfBirth.addEventListener("change", handleChange);
// IDgender.addEventListener("change", handleChange);
// IDmariteStatus.addEventListener("change", handleChange);
// IDbloodGroup.addEventListener("change", handleChange);

// IDstatus.addEventListener("change", handleChange);
// IDcontractType.addEventListener("change", handleChange);

IDdateOfJoin.addEventListener("change", handleChange);
IDdateOfGroupJoin.addEventListener("change", handleChange);
IDdateOfGratuity.addEventListener("change", handleChange);
IDdateOfTransfer.addEventListener("change", handleChange);
IDdateOfExpectedConfirmation.addEventListener("change", handleChange);
IDprobationPeriod.addEventListener("change", handleChange);
IDdateOfConfirmation.addEventListener("change", handleChange);
IDdateOfPayStructure.addEventListener("change", handleChange);

IDesic_limit.addEventListener("change", handleChange);
IDpf_enable.addEventListener("change", handleChange);
// IDbranch.addEventListener("change", handleChange);
// IDdepartment.addEventListener("change", handleChange);
// IDdesignation.addEventListener("change", handleChange);
// IDrole.addEventListener("change", handleChange);
// IDgradeTADA.addEventListener("change", handleChange);

// IDattendanceMethod.addEventListener("change", handleChange);
// IDasignSetup.addEventListener('change', handleChange);
// IDasignShift.addEventListener("change", handleChange);

IDaadhar_number.addEventListener("input", handleChange);
// IDaadhar_number.addEventListener('change', handleChange);
IDupload_aadhar.addEventListener("change", handleChange);
IDdrivng_license_number.addEventListener("input", handleChange);
IDupload_drivng_license.addEventListener("change", handleChange);
IDvoter_id_number.addEventListener("input", handleChange);
IDupload_voter_id.addEventListener("change", handleChange);
IDpassport_number.addEventListener("input", handleChange);
IDupload_passport.addEventListener("change", handleChange);
IDaccount_number.addEventListener("input", handleChange);
IDupload_passbook.addEventListener("change", handleChange);
IDpan_number.addEventListener("input", handleChange);
IDupload_pan.addEventListener("change", handleChange);

IDpfTrustCode.addEventListener("change", handleChange);
IDpfFoundMember.addEventListener("change", handleChange);
IDPfNumber.addEventListener("change", handleChange);
IDuniversalAccountNumber.addEventListener("change", handleChange);
IDvpfPercentage.addEventListener("change", handleChange);
IDpfDateOfJoining.addEventListener("change", handleChange);
IDpfDateOfLeaving.addEventListener("change", handleChange);
IDreasonOfLeavingPF.addEventListener("change", handleChange);
IDesiNumber.addEventListener("change", handleChange);
IDesiDespensary.addEventListener("change", handleChange);
IDesiDateOfJoining.addEventListener("change", handleChange);
IDesiDateOfLeaving.addEventListener("change", handleChange);
IDreasonOfLeavingESIC.addEventListener("change", handleChange);

primaryEQID.addEventListener("change", handleChange);
IDqualification.addEventListener("change", handleChange);
IDstream.addEventListener("change", handleChange);
IDcource_type.addEventListener("change", handleChange);
IDspecalization.addEventListener("change", handleChange);
IDcource_nature.addEventListener("change", handleChange);
IDquali_status.addEventListener("change", handleChange);
IDinstitute_name.addEventListener("change", handleChange);
IDuniversityName.addEventListener("change", handleChange);
IDedu_from_date.addEventListener("change", handleChange);
IDedu_to_date.addEventListener("change", handleChange);
IDpassing_date.addEventListener("change", handleChange);
IDpercentage.addEventListener("change", handleChange);
IDgrade.addEventListener("change", handleChange);
IDduration.addEventListener("change", handleChange);
IDyear.addEventListener("change", handleChange);

IDtempPinCode.addEventListener("change", handleChange);
IDpermanentPinCode.addEventListener("change", handleChange);

IDyearOfService.addEventListener("change", handleChange);
IDretirementDate.addEventListener("change", handleChange);
IDseparationSubmitDate.addEventListener("change", handleChange);
IDexpectedLeavingDate.addEventListener("change", handleChange);
IDleavingDateAsPerNoticePeriod.addEventListener("change", handleChange);
IDnoticePeriodRequiredDate.addEventListener("change", handleChange);
IDreasonForLeave.addEventListener("change", handleChange);
IDleaveDate.addEventListener("change", handleChange);
IDnoticePeriodServedDate.addEventListener("change", handleChange);
IDsettlementFrom.addEventListener("change", handleChange);
IDfinalSettlementDate.addEventListener("change", handleChange);
IDnoticePeriodShortfallDays.addEventListener("change", handleChange);
IDexitInterviewDate.addEventListener("change", handleChange);
IDlastWorkingDate.addEventListener("change", handleChange);
IDremark.addEventListener("change", handleChange);
IDemployerNoticePeriod.addEventListener("change", handleChange);
IDemployeeNoticePeriod.addEventListener("change", handleChange);

IDifsc.addEventListener("change", handleChange);
IDaccountCode.addEventListener("change", handleChange);
IDbankName.addEventListener("change", handleChange);
IDbranchName.addEventListener("change", handleChange);
IDbank_account_number.addEventListener("change", handleChange);
IDMICR.addEventListener("change", handleChange);
IDbranchCode.addEventListener("change", handleChange);
// IDbudgetCode.addEventListener("change", handleChange);
// IDcompanyName.addEventListener("change", handleChange);
// IDcompanyName2.addEventListener("change", handleChange);

// Add input event listeners for Permanent Address Fields
IDpermanentSearchInput.addEventListener("input", handleChange);
IDpermanentLongitude.addEventListener("input", handleChange);
IDpermanentLatitude.addEventListener("input", handleChange);
IDpermanentPinCode.addEventListener("input", handleChange);

// Add input event listeners for Temporary Address Fields
IDtemporarySearchInput.addEventListener("input", handleChange);
IDtemporaryLongitude.addEventListener("input", handleChange);
IDtemporaryLatitude.addEventListener("input", handleChange);
IDtempPinCode.addEventListener("input", handleChange);


function inputClick(event) {
    // Find the file input within the clicked span
    let fileInput = event.currentTarget.querySelector("#profileInput");
    if (fileInput) {
        fileInput.click(); // Simulate a click on the file input
    }
}

// function profileSet() {
//     if (IDemployee_id.value && EmpNotAlreadyExist && EmailNotAlreadyExist && PhoneNotAlreadyExist) {
//         var file = fileInput.files[0];
//         const fileType = file.type;
//         if (file && (fileType === 'image/jpeg' || fileType === 'image/png' || fileType === 'image/gif')) {
//             var reader = new FileReader();
//             reader.onload = function (e) {
//                 avtarEmp.style.backgroundImage = 'url(' + e.target.result + ')';
//             };
//             reader.readAsDataURL(file);
//             // uploadFile();
//         } else {
//             alert('invalid file formate')
//         }
//     } else {
//         Swal.fire({
//             icon: 'error',
//             text: 'Enter Employee Id.',
//         });
//     }

// }

function profileSet() {
    // Handle the profile image selection logic here
    // Example: Retrieve selected file and update the span's background image
    let fileInput = document.getElementById("profileInput");
    if (fileInput.files && fileInput.files[0]) {
        let reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById(
                "empAvtar"
            ).style.backgroundImage = `url('${e.target.result}')`;
            document.getElementById(
                "empAvtar2"
            ).style.backgroundImage = `url('${e.target.result}')`;
        };
        reader.readAsDataURL(fileInput.files[0]);
    }
}

// function uploadFile() {
//     var file = fileInput.files[0];
//     var formData = new FormData();
//     formData.append('avatar', file);
//     formData.append('_token', CSRF);
//     formData.append('emp_id', IDemployee_id.value);

//     $.ajax({
//         url: profileURL,
//         type: "POST",
//         data: formData,
//         contentType: false,
//         processData: false,
//         dataType: 'json',
//         cache: false,
//         success: function (res) {
//             profilePath = res.path;
//             Swal.fire({
//                 icon: 'success',
//                 text: 'Photo saved successfully.',
//             });
//         },
//         error: function (xhr, status, error) {
//             Swal.fire({
//                 icon: 'error',
//                 text: 'Data save failed.',
//             });
//         }
//     });
// }

function fetchData() {
    var formData = new FormData();
    formData.append("_token", CSRF);
    // formData.append('emp_id', IDemployee_id.value);
    // formData.append('emp_id', 'DEV0023');
    formData.append("emp_id", IDemployee_id.value);
    $.ajax({
        url: getEmpURL,
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "json",
        cache: false,
        success: function (res) {
            var employee = res.data;

            var table = document.createElement("table");

            for (var key in employee) {
                if (employee.hasOwnProperty(key)) {
                    var row = table.insertRow();
                    var cell1 = row.insertCell(0);
                    var cell2 = row.insertCell(1);
                    cell1.innerHTML = key;
                    cell2.innerHTML = employee[key];
                }
            }

            dataCard.innerHTML = "";
            dataCard.appendChild(table);
        },
        error: function (xhr, status, error) {
            Swal.fire({
                icon: "error",
                text: "Data save failed.",
                timer: 3000,
            });
        },
    });
}
if (IDprimary_id) {
    // handleChange();
    namePrint();
    EmailText.innerHTML = IDemail.value;
}

IDtempPinCode.addEventListener("input", updateErrorMessages2);
IDpermanentPinCode.addEventListener("input", updateErrorMessages2);

// IDstatus.addEventListener("change", updateErrorMessages2);
// IDcontractType.addEventListener("change", updateErrorMessages2);
// IDdateOfJoin.addEventListener("input", updateErrorMessages2);
// IDemployeeJobStatus.addEventListener("change", updateErrorMessages2);

IDbranch.addEventListener("change", updateErrorMessages5);
IDdepartment.addEventListener("change", updateErrorMessages5);
IDdesignation.addEventListener("change", updateErrorMessages5);
IDgradeTADA.addEventListener("change", updateErrorMessages5);
IDrole.addEventListener("change", updateErrorMessages5);
IDreportManager.addEventListener("change", updateErrorMessages5);
IDbudgetCode.addEventListener("input", updateErrorMessages5);
// IDcompanyName.addEventListener("input", updateErrorMessages5);
// IDdesignationName.addEventListener("input", updateErrorMessages5);
// IDjoiningPeriod.addEventListener("input", updateErrorMessages5);
// IDcompanyName2.addEventListener("input", updateErrorMessages5);
// IDdesignationName2.addEventListener("input", updateErrorMessages5);
// IDjoiningPeriod2.addEventListener("input", updateErrorMessages5);
// IDaccountCode.addEventListener("input", updateErrorMessages4);

function showTab(index) {
    // Hide all content sections
    const allContent = document.querySelectorAll('.tab-pane');
    allContent.forEach(function (content) {
        // content.style.display = 'none';  // This hides the content by setting the display to 'none'
        content.classList.add('d-none'); // This adds the Bootstrap class 'd-none'
    });

    const allContentf = document.querySelectorAll('.card-footer');
    allContentf.forEach(function (content) {
        // content.style.display = 'none';  // This hides the content by setting the display to 'none'
        content.classList.add('d-none'); // This adds the Bootstrap class 'd-none'
    });

    const tabCard = document.querySelectorAll('.tabCard');
    tabCard.forEach(function (content) {
        // content.style.display = 'none';  // This hides the content by setting the display to 'none'
        content.classList.add('d-none'); // This adds the Bootstrap class 'd-none'
    });


    const selectedContenttabCard = document.getElementById(`tabCard${index}`);

    if (selectedContenttabCard) {
        // selectedContenttabCard.style.display = 'block'; // Make the element visible
        selectedContenttabCard.classList.remove('d-none'); // Remove the 'd-none' class to ensure it's visible
    }

    if (index == 10) {
        document.getElementById('sidebard-employee-card').classList.add('d-none');
    } else {
        document.getElementById('sidebard-employee-card').classList.remove('d-none');
    }

    // // Show the selected tab's content
    // const selectedContent = document.getElementById('content-' + index);
    // if (selectedContent) {
    //     selectedContent.style.display = 'block';
    // }

    const selectedContent = document.getElementById(`tab${index}`);

    if (selectedContent) {
        // selectedContent.style.display = 'block'; // Make the element visible
        selectedContent.classList.remove('d-none'); // Remove the 'd-none' class to ensure it's visible
    }


    const selectedContentBtn = document.getElementById('tab' + index + 'btns');
    if (selectedContentBtn) {
        // selectedContentBtn.style.display = 'block';
        selectedContentBtn.classList.remove('d-none'); // Remove the 'd-none' class to ensure it's visible
    }

    // Remove active class from all tabs
    const allTabs = document.querySelectorAll('.nav-link');
    allTabs.forEach(function (tab) {
        tab.style.backgroundColor = '#e0e0e0';
    });

    // Add active class to the selected tab
    const selectedTab = document.getElementById('tab-' + index);
    if (selectedTab) {
        selectedTab.style.backgroundColor = '#eeeeee'; // Active tab color
    }
}
