"use strict";

var tabBtn1 = document.getElementById('tab1btns');
var tabBtn2 = document.getElementById('tab2btns');
var tabBtn3 = document.getElementById('tab3btns');
var tabBtn4 = document.getElementById('tab4btns');
var tabBtn5 = document.getElementById('tab5btns');
var tabBtn6 = document.getElementById('tab6btns');
var tabBtn7 = document.getElementById('tab7btns');
var tabBtn8 = document.getElementById('tab8btns');
var tabBtn9 = document.getElementById('tab9btns');
var tabBtn10 = document.getElementById('tab10btns');
var tab1s = document.getElementById('tab1');
var tab2s = document.getElementById('tab2');
var tab3s = document.getElementById('tab3');
var tab4s = document.getElementById('tab4');
var tab5s = document.getElementById('tab5');
var tab6s = document.getElementById('tab6');
var tab7s = document.getElementById('tab7');
var tab8s = document.getElementById('tab8');
var tab9s = document.getElementById('tab9');
var tab10s = document.getElementById('tab10');
var tabCard1s = document.getElementById('tabCard1');
var tabCard2s = document.getElementById('tabCard2');
var tabCard3s = document.getElementById('tabCard3');
var tabCard4s = document.getElementById('tabCard4');
var tabCard5s = document.getElementById('tabCard5');
var tabCard6s = document.getElementById('tabCard6');
var tabCard7s = document.getElementById('tabCard7');
var tabCard8s = document.getElementById('tabCard8');
var tabCard9s = document.getElementById('tabCard9');
var tabCard10s = document.getElementById('tabCard10');
var IDemployee_id = document.getElementById('employee_id');
var IDEmpIdError = document.getElementById('EmpIdError');
var IDfirstName = document.getElementById('firstName');
var IDfirstNameError = document.getElementById('firstNameError');
var IDmiddleName = document.getElementById('middleName');
var IDmiddleNameError = document.getElementById('middleNameError');
var IDlastName = document.getElementById('lastName');
var IDlastNameError = document.getElementById('lastNameError');
var IDcontact = document.getElementById('contact');
var IDcontactError = document.getElementById('contactError');
var IDemail = document.getElementById('email');
var IDemailError = document.getElementById('emailError');
var IDdateOfBirth = document.getElementById('dateOfBirth');
var IDdateOfBirthError = document.getElementById('dateOfBirthError');
var IDgender = document.getElementById('gender');
var IDgenderError = document.getElementById('genderError');
var IDmariteStatus = document.getElementById('mariteStatus');
var IDmariteStatusError = document.getElementById('mariteStatusError');
var IDbloodGroup = document.getElementById('bloodGroup');
var IDbloodGroupError = document.getElementById('bloodGroupError');
var IDstatus = document.getElementById('status');
var IDstatusError = document.getElementById('statusError');
var IDcontractType = document.getElementById('contractType');
var IDcontractTypeError = document.getElementById('contractTypeError');
var IDdateOfJoin = document.getElementById('dateOfJoin');
var IDdateOfJoinError = document.getElementById('dateOfJoinError');
var IDdateOfGroupJoin = document.getElementById('dateOfGroupJoin');
var IDdateOfGroupJoinError = document.getElementById('dateOfGroupJoinError');
var IDdateOfGratuity = document.getElementById('dateOfGratuity');
var IDdateOfGratuityError = document.getElementById('dateOfGratuityError');
var IDdateOfTransfer = document.getElementById('dateOfTransfer');
var IDdateOfTransferError = document.getElementById('dateOfTransferError');
var IDdateOfExpectedConfirmation = document.getElementById('dateOfExpectedConfirmation');
var IDdateOfExpectedConfirmationError = document.getElementById('dateOfExpectedConfirmationError');
var IDprobationPeriod = document.getElementById('probationPeriod');
var IDprobationPeriodError = document.getElementById('probationPeriodError');
var IDdateOfConfirmation = document.getElementById('dateOfConfirmation');
var IDdateOfConfirmationError = document.getElementById('dateOfConfirmationError');
var IDdateOfPayStructure = document.getElementById('dateOfPayStructure');
var IDdateOfPayStructureError = document.getElementById('dateOfPayStructureError');
var IDesic_limit = document.getElementById('esic_limit');
var IDbranch = document.getElementById('branch');
var IDbranchError = document.getElementById('branchError');
var IDdepartment = document.getElementById('department');
var IDdepartmentError = document.getElementById('departmentError');
var IDdesignation = document.getElementById('designation');
var IDdesignationError = document.getElementById('designationError');
var IDattendanceMethod = document.getElementById('attendanceMethod');
var IDattendanceMethodError = document.getElementById('attendanceMethodError');
var IDasignSetup = document.getElementById('asignSetup');
var IDasignSetupError = document.getElementById('asignSetupError');
var IDasignShift = document.getElementById('asignShift');
var IDasignShiftError = document.getElementById('asignShiftError');
var IDaadhar_number = document.getElementById('aadhar_number');
var IDaadhar_numberError = document.getElementById('aadhar_numberError');
var IDupload_aadhar = document.getElementById('upload_aadhar');
var IDupload_aadharError = document.getElementById('upload_aadharError');
var IDdrivng_license_number = document.getElementById('drivng_license_number');
var IDdrivng_license_numberError = document.getElementById('drivng_license_numberError');
var IDupload_drivng_license = document.getElementById('upload_drivng_license');
var IDupload_drivng_licenseError = document.getElementById('upload_drivng_licenseError');
var IDvoter_id_number = document.getElementById('voter_id_number');
var IDvoter_id_numberError = document.getElementById('voter_id_numberError');
var IDupload_voter_id = document.getElementById('upload_voter_id');
var IDupload_voter_idError = document.getElementById('upload_voter_idError');
var IDpassport_number = document.getElementById('passport_number');
var IDpassport_numberError = document.getElementById('passport_numberError');
var IDupload_passport = document.getElementById('upload_passport');
var IDupload_passportError = document.getElementById('upload_passportError');
var IDaccount_number = document.getElementById('account_number');
var IDaccount_numberError = document.getElementById('account_numberError');
var IDupload_passbook = document.getElementById('upload_account');
var IDupload_passbookError = document.getElementById('upload_passbookError');
var IDpfTrustCode = document.getElementById('pfTrustCode');
var IDpfTrustCodeError = document.getElementById('pfTrustCodeError');
var IDpfFoundMember = document.getElementById('pfFoundMember');
var IDpfFoundMemberError = document.getElementById('pfFoundMemberError');
var IDPfNumber = document.getElementById('PfNumber');
var IDPfNumberError = document.getElementById('PfNumberError');
var IDuniversalAccountNumber = document.getElementById('universalAccountNumber');
var IDuniversalAccountNumberError = document.getElementById('universalAccountNumberError');
var IDvpfPercentage = document.getElementById('vpfPercentage');
var IDvpfPercentageError = document.getElementById('vpfPercentageError');
var IDpfDateOfJoining = document.getElementById('pfDateOfJoining');
var IDpfDateOfJoiningError = document.getElementById('pfDateOfJoiningError');
var IDpfDateOfLeaving = document.getElementById('pfDateOfLeaving');
var IDpfDateOfLeavingError = document.getElementById('pfDateOfLeavingError');
var IDreasonOfLeavingPF = document.getElementById('reasonOfLeavingPF');
var IDreasonOfLeavingPFError = document.getElementById('reasonOfLeavingPFError');
var IDesiNumber = document.getElementById('esiNumber');
var IDesiNumberError = document.getElementById('esiNumberError');
var IDesiDespensary = document.getElementById('esiDespensary');
var IDesiDespensaryError = document.getElementById('esiDespensaryError');
var IDesiDateOfJoining = document.getElementById('esiDateOfJoining');
var IDesiDateOfJoiningError = document.getElementById('esiDateOfJoiningError');
var IDesiDateOfLeaving = document.getElementById('esiDateOfLeaving');
var IDesiDateOfLeavingError = document.getElementById('esiDateOfLeavingError');
var IDreasonOfLeavingESIC = document.getElementById('reasonOfLeavingESIC');
var IDreasonOfLeavingESICError = document.getElementById('reasonOfLeavingESICError');
var IDqualification = document.getElementById('qualification');
var IDstream = document.getElementById('stream');
var IDcource_type = document.getElementById('cource_type');
var IDspecalization = document.getElementById('specalization');
var IDcource_nature = document.getElementById('cource_nature');
var IDquali_status = document.getElementById('quali_status');
var IDinstitute_name = document.getElementById('institute_name');
var IDuniversityName = document.getElementById('universityName');
var IDedu_from_date = document.getElementById('edu_from_date');
var IDedu_to_date = document.getElementById('edu_to_date');
var IDpassing_date = document.getElementById('passing_date');
var IDpercentage = document.getElementById('percentage');
var IDgrade = document.getElementById('grade');
var IDduration = document.getElementById('duration');
var IDyear = document.getElementById('year');
var IDtempCountry = document.getElementById('tempCountry');
var IDtempState = document.getElementById('tempState');
var IDtempCity = document.getElementById('tempCity');
var IDtempPinCode = document.getElementById('tempPinCode');
var IDtempAddress = document.getElementById('tempAddress');
var IDtempSameAsParmanant = document.getElementById('tempSameAsParmanant');
var IDpermanentCountry = document.getElementById('permanentCountry');
var IDpermanentState = document.getElementById('permanentState');
var IDpermanentCity = document.getElementById('permanentCity');
var IDpermanentPinCode = document.getElementById('permanentPinCode');
var IDpermanentAddress = document.getElementById('permanentAddress');
var IDyearOfService = document.getElementById('yearOfService');
var IDretirementDate = document.getElementById('retirementDate');
var IDseparationSubmitDate = document.getElementById('separationSubmitDate');
var IDexpectedLeavingDate = document.getElementById('expectedLeavingDate');
var IDleavingDateAsPerNoticePeriod = document.getElementById('leavingDateAsPerNoticePeriod');
var IDnoticePeriodRequiredDate = document.getElementById('noticePeriodRequiredDate');
var IDreasonForLeave = document.getElementById('reasonForLeave');
var IDleaveDate = document.getElementById('leaveDate');
var IDnoticePeriodServedDate = document.getElementById('noticePeriodServedDate');
var IDsettlementFrom = document.getElementById('settlementFrom');
var IDfinalSettlementDate = document.getElementById('finalSettlementDate');
var IDnoticePeriodShortfallDays = document.getElementById('noticePeriodShortfallDays');
var IDexitInterviewDate = document.getElementById('exitInterviewDate');
var IDlastWorkingDate = document.getElementById('lastWorkingDate');
var IDremark = document.getElementById('remark');
var IDemployerNoticePeriod = document.getElementById('employerNoticePeriod');
var IDemployeeNoticePeriod = document.getElementById('employeeNoticePeriod');
var IDifsc = document.getElementById('ifsc');
var IDIFSCError = document.getElementById('IFSCError');
var IDbankName = document.getElementById('bankName');
var IDbankNameError = document.getElementById('bankNameError');
var IDbranchName = document.getElementById('branchName');
var IDbranchNameError = document.getElementById('branchNameError');
var IDbank_account_number = document.getElementById('bank_account_number');
var IDbank_account_numberError = document.getElementById('bank_account_numberError');
var IDlast_sd = document.getElementById('last_sd');
var IDMICR = document.getElementById('micr');
var IDbranchCode = document.getElementById('branch_code');
var IDemployeeName = document.getElementById('employeeName');
var EmailText = document.getElementById('emailText');
var empIDText = document.getElementById("EmpIDText");
var contactText = document.getElementById("ContactText");
var birthdayText = document.getElementById("birthdayText");
var genderText = document.getElementById("genderText");
var martialText = document.getElementById("martialText");
var bloodGroupText = document.getElementById("bloodGrouptext");
var activeText = document.getElementById("activeText");
var contractText = document.getElementById("contractText");
var joiningDateText = document.getElementById("joiningDateText");
var groupJoiningText = document.getElementById("groupJoiningText");
var gratuityDateText = document.getElementById("gratuityDateText");
var transferDateText = document.getElementById("transferDateText");
var expectedConfirmationText = document.getElementById("expectedConfirmationText");
var probationDateText = document.getElementById("probationDateText");
var confirmationDateText = document.getElementById("confirmationDateText");
var payStructureDateText = document.getElementById("payStructureDateText");
var esicLimitText = document.getElementById("esicLimitText");
var branchText = document.getElementById("branchText");
var departmentText = document.getElementById("departmentText");
var designationText = document.getElementById("designationText");
var assignMethodText = document.getElementById("assignMethodText");
var assignSetupText = document.getElementById("assignSetupText");
var shiftPolicyText = document.getElementById("shiftPolicyText");
var assignShift = document.getElementById("assignShift");
var aadhardNumberText = document.getElementById("aadhardNumberText");
var drivingLicenseText = document.getElementById("drivingLicenseText");
var electionCardText = document.getElementById("electionCardText");
var passportText = document.getElementById("passportText");
var bankAcNumText = document.getElementById("bankAcNumText");
var pftrustCode = document.getElementById('pftrustCodeText');
var pensionMember = document.getElementById('pensionMemberText');
var pfNum = document.getElementById('pfNumText'); // First occurrence of pfNumText

var uniAcNum = document.getElementById('uniAcNumText');
var vpfPerc = document.getElementById('vpfPercText');
var pfJoinDate = document.getElementById('pfJoinDateText');
var pfLeaveDate = document.getElementById('pfLeaveDateText');
var PfLeaveReason = document.getElementById('PfLeaveReasonText');
var esicNum = document.getElementById('esicNumText');
var esiDesperacy = document.getElementById('esiDesperacyText');
var esicDateOfJoining = document.getElementById('esicDateOfJoiningText');
var esicDateOfLeave = document.getElementById('esicDateOfLeaveText');
var esicLeaveReason = document.getElementById('esicLeaveReasonText');
var qualification = document.getElementById('qualificationText');
var stream = document.getElementById('streamText');
var courseType = document.getElementById('courseTypeText');
var specialization = document.getElementById('SpecializationText');
var natureOfCourse = document.getElementById('NatureofCourseText');
var qualificationStatus = document.getElementById('QualificationStatusText');
var instituteName = document.getElementById('InstituteNameText');
var universityName = document.getElementById('UniversityNameText');
var fromDate = document.getElementById('FromDateText');
var toDate = document.getElementById('ToDateText');
var passingDate = document.getElementById('PassingDateText');
var percentage = document.getElementById('PercentageText');
var grade = document.getElementById('GradeText');
var durationOfCourse = document.getElementById('DurationofCourseText');
var year = document.getElementById('YearText');
var yearOfServiceElement = document.getElementById('YearofServiceText');
var retirementDateElement = document.getElementById('RetirementDateText');
var separationSubmitOnElement = document.getElementById('SeprationSubmitOnText');
var expectedLeavingDateElement = document.getElementById('ExpectedLeavingDateText');
var leavingDatePeriodElement = document.getElementById('LeavingDatePeriodText');
var noticePeriodDaysElement = document.getElementById('NoticePeriodDaysText');
var reasonForLeavingElement = document.getElementById('ReasonForLeavingText');
var leavingDateElement = document.getElementById('LeavingDateText');
var noticeServedDaysElement = document.getElementById('NoticeServedDaysText');
var settlementFromElement = document.getElementById('SettlementFromText');
var finalSettlementDateElement = document.getElementById('FinalSettlementDateText');
var noticePeriodShortfallDaysElement = document.getElementById('NoticePeriodShorftfallDaysText');
var exitInterviewDateElement = document.getElementById('ExitInterviewDateText');
var lastWorkingDateElement = document.getElementById('LastWorkingDateText');
var remarkElement = document.getElementById('RemarkText');
var noticePeriodForEmployerElement = document.getElementById('NoticePeriodForEmployerText');
var noticePeriodForEmployeeElement = document.getElementById('NoticePeriodForEmployeeText');
var ifscElement = document.getElementById("ifscText");
var bankNameElement = document.getElementById("BankNameText");
var branchNameElement = document.getElementById("BranchNameText");
var micrElement = document.getElementById("MICRText");
var bankCodeElement = document.getElementById("BankCodeText"); // var bankAccountNumberElement = document.getElementById("BankAccountNumberText");
// var BankCodeTextElement = document.getElementById("BankCodeText");

function namePrint(e) {
  IDemployeeName.innerHTML = IDfirstName.value + ' ' + IDmiddleName.value + ' ' + IDlastName.value;
}

function saveData(state, action) {
  if (state == 1) {
    console.log(EmpNotAlreadyExist);

    if (IDfirstName.value && IDemployee_id.value && IDcontact.value && IDemail.value && IDdateOfBirth.value && IDgender.value && IDmariteStatus.value && IDbloodGroup.value && IDcontact.value.length == 10 && isValidEmail(IDemail.value) && EmpNotAlreadyExist && EmailNotAlreadyExist && PhoneNotAlreadyExist) {
      $.ajax({
        url: formSubmitURL,
        type: "POST",
        data: {
          _token: CSRF,
          id: 1,
          emp_id: IDemployee_id.value,
          firstName: IDfirstName.value,
          secondName: IDmiddleName.value,
          lastName: IDlastName.value,
          contact: IDcontact.value,
          email: IDemail.value,
          dob: IDdateOfBirth.value,
          gender: IDgender.value,
          marital: IDmariteStatus.value,
          bloodGroup: IDbloodGroup.value
        },
        dataType: 'json',
        cache: true,
        success: function success(res) {
          Swal.fire({
            icon: 'success',
            // title: 'Succes',
            text: 'Data save successfully.',
            timer: 3000,
          });
          changeTab(state, 0);
        }
      });
    } else {
      if (!IDfirstName.value) {
        IDfirstNameError.innerHTML = 'This field cannot be empty.';
      } else {
        IDfirstNameError.innerHTML = '';
      }

      if (!IDemployee_id.value) {
        IDEmpIdError.innerHTML = 'Employee ID cannot be empty.';
      } else {
        IDEmpIdError.innerHTML = '';
      }

      if (!IDcontact.value) {
        IDcontactError.innerHTML = 'Contact number cannot be empty.';
      } else {
        IDcontactError.innerHTML = '';
      }

      if (!IDemail.value) {
        IDemailError.innerHTML = 'Email address cannot be empty.';
      } else {
        IDemailError.innerHTML = '';
      }

      if (!isValidEmail(IDemail.value)) {
        IDemailError.innerHTML = 'Enter valid email.';
      } else {
        IDemailError.innerHTML = '';
      }

      if (!IDdateOfBirth.value) {
        IDdateOfBirthError.innerHTML = 'Date of birth cannot be empty.';
      } else {
        IDdateOfBirthError.innerHTML = '';
      }

      if (!IDgender.value) {
        IDgenderError.innerHTML = 'Gender cannot be empty.';
      } else {
        IDgenderError.innerHTML = '';
      }

      if (!IDmariteStatus.value) {
        IDmariteStatusError.innerHTML = 'Marital status cannot be empty.';
      } else {
        IDmariteStatusError.innerHTML = '';
      }

      if (!IDbloodGroup.value) {
        IDbloodGroupError.innerHTML = 'Blood group cannot be empty.';
      } else {
        IDbloodGroupError.innerHTML = '';
      }

      if (IDcontact.value.length != 10) {
        IDcontactError.innerHTML = 'Contact number should be 10 digits.';
      } else {
        IDcontactError.innerHTML = '';
      }
    }
  } else if (state == 2) {
    if (IDstatus.value && IDcontractType.value && IDdateOfJoin.value) {
      $.ajax({
        url: formSubmitURL,
        type: "POST",
        data: {
          _token: CSRF,
          id: 2,
          emp_id: IDemployee_id.value,
          active_status: IDstatus.value,
          contract_type: IDcontractType.value,
          doj: IDdateOfJoin.value,
          dogj: IDdateOfGroupJoin.value,
          dog: IDdateOfGratuity.value,
          dot: IDdateOfTransfer.value,
          doec: IDdateOfExpectedConfirmation.value,
          probation_days: IDprobationPeriod.value,
          doc: IDdateOfConfirmation.value,
          dops: IDdateOfPayStructure.value
        },
        dataType: 'json',
        cache: true,
        success: function success(res) {
          Swal.fire({
            icon: 'success',
            // title: 'Succes',
            text: 'Data save successfully.',
            timer: 3000,
          });
          changeTab(state, 0);
        }
      });
    } else {
      if (!IDemployee_id.value) {
        IDEmpIdError.innerHTML = 'Employee ID cannot be empty.';
        changeTab(0, state);
      } else {
        IDEmpIdError.innerHTML = '';
      }

      if (!IDstatus.value) {
        IDstatusError.innerHTML = 'This field can not be empty';
      } else {
        IDstatusError.innerHTML = '';
      }

      if (!IDcontractType.value) {
        IDcontractTypeError.innerHTML = 'This field can not be empty';
      } else {
        IDcontractTypeError.innerHTML = '';
      }

      if (!IDdateOfJoin.value) {
        IDdateOfJoinError.innerHTML = 'This field can not be empty';
      } else {
        IDdateOfJoinError.innerHTML = '';
      }
    }
  } else if (state == 3) {
    if (IDbranch.value && IDdepartment.value && IDdesignation.value) {
      $.ajax({
        url: formSubmitURL,
        type: "POST",
        data: {
          _token: CSRF,
          id: 3,
          emp_id: IDemployee_id.value,
          esic_limit: IDesic_limit.value,
          branch: IDbranch.value,
          department: IDdepartment.value,
          designation: IDdesignation.value
        },
        dataType: 'json',
        cache: true,
        success: function success(res) {
          Swal.fire({
            icon: 'success',
            // title: 'Succes',
            text: 'Data save successfully.',
            timer: 3000,
          });
          changeTab(state, 0);
        }
      });
    } else {
      if (!IDemployee_id.value) {
        IDEmpIdError.innerHTML = 'This field cannot be empty.';
        changeTab(0, state);
      } else {
        IDEmpIdError.innerHTML = '';
      }

      if (!IDbranch.value) {
        IDbranchError.innerHTML = 'This field cannot be empty.';
      } else {
        IDbranchError.innerHTML = '';
      }

      if (!IDdepartment.value) {
        IDdepartmentError.innerHTML = 'This field cannot be empty.';
      } else {
        IDdepartmentError.innerHTML = '';
      }

      if (!IDdesignation.value) {
        IDdesignationError.innerHTML = 'This field cannot be empty.';
      } else {
        IDdesignationError.innerHTML = '';
      }
    }
  } else if (state == 4) {
    if (IDattendanceMethod.value && IDasignSetup.value && IDasignShift.value) {
      $.ajax({
        url: formSubmitURL,
        type: "POST",
        data: {
          _token: CSRF,
          id: 4,
          emp_id: IDemployee_id.value,
          attendance_method: IDattendanceMethod.value,
          assign_setup: IDasignSetup.value,
          assign_shift: IDasignShift.value
        },
        dataType: 'json',
        cache: true,
        success: function success(res) {
          Swal.fire({
            icon: 'success',
            // title: 'Succes',
            text: 'Data save successfully.',
            timer: 3000,
          });
          changeTab(state, 0);
        }
      });
    } else {
      if (!IDemployee_id.value) {
        IDEmpIdError.innerHTML = 'This field cannot be empty.';
        changeTab(0, state);
      } else {
        IDEmpIdError.innerHTML = '';
      }

      if (!IDattendanceMethod.value) {
        IDattendanceMethodError.innerHTML = 'This field cannot be empty.';
      } else {
        IDattendanceMethodError.innerHTML = '';
      }

      if (!IDasignSetup.value) {
        IDasignSetupError.innerHTML = 'This field cannot be empty.';
      } else {
        IDasignSetupError.innerHTML = '';
      }

      if (!IDasignShift.value) {
        IDasignShiftError.innerHTML = 'This field cannot be empty.';
      } else {
        IDasignShiftError.innerHTML = '';
      }
    }
  } else if (state == 5) {
    uploadDocs(); // var file_aadhar = IDupload_aadhar.files[0];
    // var file_drivng_license = IDupload_drivng_license.files[0];
    // var file_voter_id = IDupload_voter_id.files[0];
    // var file_passport = IDupload_passport.files[0];
    // var file_passbook = IDupload_passbook.files[0];
    // var formData = new FormData();
    // formData.append('_token', CSRF);
    // formData.append('id', 5);
    // formData.append('aadharUpload', file_aadhar);
    // formData.append('drivingUpload', file_drivng_license);
    // formData.append('voterUpload', file_voter_id);
    // formData.append('passportUpload', file_passport);
    // formData.append('passbookUpload', file_passbook);
    // formData.append('emp_id', IDemployee_id.value);
    // formData.append('aadhar_number', IDaadhar_number.value);
    // formData.append('drivng_license_number', IDdrivng_license_number.value);
    // formData.append('voter_id_number', IDvoter_id_number.value);
    // formData.append('passport_number', passport_number.value);
    // formData.append('account_number', IDaccount_number.value);
    // $.ajax({
    //     url: formSubmitURL,
    //     type: "POST",
    //     data: formData,
    //     dataType: 'json',
    //     cache: true,
    //     success: function (res) {
    //         console.log(res);
    //         // changeTab(state, 0);
    //     }
    // });
    // changeTab(state, 0);
    // $.ajax({
    //     url: formSubmitURL,
    //     type: "POST",
    //     data: {
    //         _token: CSRF,
    //         id: 5,
    //         emp_id: IDemployee_id.value,
    //         aadhar_number: IDaadhar_number.value,
    //         upload_aadhar: IDupload_aadhar.value,
    //         drivng_license_number: IDdrivng_license_number.value,
    //         upload_drivng_license: IDupload_drivng_license.value,
    //         voter_id_number: IDvoter_id_number.value,
    //         upload_voter_id: IDupload_voter_id.value,
    //         passport_number: passport_number.value,
    //         upload_passport: IDupload_passport.value,
    //         account_number: IDaccount_number.value,
    //         upload_account: IDupload_passbook.value,
    //     },
    //     dataType: 'json',
    //     cache: true,
    //     success: function (res) {
    //
    //         changeTab(state, 0);
    //     }
    // });
  } else if (state == 6) {
    $.ajax({
      url: formSubmitURL,
      type: "POST",
      data: {
        _token: CSRF,
        id: 6,
        emp_id: IDemployee_id.value,
        pfTrustCode: IDpfTrustCode.value,
        pfFoundMember: IDpfFoundMember.value,
        PfNumber: IDPfNumber.value,
        universalAccountNumber: IDuniversalAccountNumber.value,
        vpfPercentage: IDvpfPercentage.value,
        pfDateOfJoining: IDpfDateOfJoining.value,
        pfDateOfLeaving: IDpfDateOfLeaving.value,
        reasonOfLeavingPF: IDreasonOfLeavingPF.value,
        esiNumber: IDesiNumber.value,
        esiDespensary: IDesiDespensary.value,
        esiDateOfJoining: IDesiDateOfJoining.value,
        esiDateOfLeaving: IDesiDateOfLeaving.value,
        reasonOfLeavingESIC: IDreasonOfLeavingESIC.value
      },
      dataType: 'json',
      cache: true,
      success: function success(res) {
        changeTab(state, 0);
      }
    });
  } else if (state == 7) {
    $.ajax({
      url: formSubmitURL,
      type: "POST",
      data: {
        _token: CSRF,
        id: 7,
        emp_id: IDemployee_id.value,
        qualification: IDqualification.value,
        stream: IDstream.value,
        cource_type: IDcource_type.value,
        specalization: IDspecalization.value,
        cource_nature: IDcource_nature.value,
        quali_status: IDquali_status.value,
        institute_name: IDinstitute_name.value,
        universityName: IDuniversityName.value,
        edu_from_date: IDedu_from_date.value,
        edu_to_date: IDedu_to_date.value,
        passing_date: IDpassing_date.value,
        percentage: IDpercentage.value,
        grade: IDgrade.value,
        duration: IDduration.value,
        year: IDyear.value,
        tempCountry: IDtempCountry.value,
        tempState: IDtempState.value,
        tempCity: IDtempCity.value,
        tempPinCode: IDtempPinCode.value,
        tempAddress: IDtempAddress.value,
        tempSameAsParmanant: IDtempSameAsParmanant.value,
        permanentCountry: IDpermanentCountry.value,
        permanentState: IDpermanentState.value,
        permanentCity: IDpermanentCity.value,
        permanentPinCode: IDpermanentPinCode.value,
        permanentAddress: IDpermanentAddress.value
      },
      dataType: 'json',
      cache: true,
      success: function success(res) {
        changeTab(state, 0);
      }
    });
  } else if (state == 8) {
    $.ajax({
      url: formSubmitURL,
      type: "POST",
      data: {
        _token: CSRF,
        id: 8,
        emp_id: IDemployee_id.value,
        yearOfService: IDyearOfService.value,
        retirementDate: IDretirementDate.value,
        separationSubmitDate: IDseparationSubmitDate.value,
        expectedLeavingDate: IDexpectedLeavingDate.value,
        leavingDateAsPerNoticePeriod: IDleavingDateAsPerNoticePeriod.value,
        noticePeriodRequiredDate: IDnoticePeriodRequiredDate.value,
        reasonForLeave: IDreasonForLeave.value,
        leaveDate: IDleaveDate.value,
        noticePeriodServedDate: IDnoticePeriodServedDate.value,
        settlementFrom: IDsettlementFrom.value,
        finalSettlementDate: IDfinalSettlementDate.value,
        noticePeriodShortfallDays: IDnoticePeriodShortfallDays.value,
        exitInterviewDate: IDexitInterviewDate.value,
        lastWorkingDate: IDlastWorkingDate.value,
        remark: IDremark.value,
        employerNoticePeriod: IDemployerNoticePeriod.value,
        employeeNoticePeriod: IDemployeeNoticePeriod.value
      },
      dataType: 'json',
      cache: true,
      success: function success(res) {
        Swal.fire({
          icon: 'success',
          // title: 'Succes',
          text: 'Data save successfully.',
          timer: 3000,
        });
        changeTab(state, 0);
      }
    });
  } else if (state == 9) {
    $.ajax({
      url: formSubmitURL,
      type: "POST",
      data: {
        _token: CSRF,
        id: 8,
        emp_id: IDemployee_id.value,
        ifsc: IDifsc.value,
        bankName: IDbankName.value,
        branchName: IDbranchName.value,
        bank_account_number: IDbank_account_number.value,
        micr_code: IDMICR.value,
        branch_code: IDbranchCode.value
      },
      dataType: 'json',
      cache: true,
      success: function success(res) {
        Swal.fire({
          icon: 'success',
          // title: 'Succes',
          text: 'Data save successfully.',
          timer: 3000,
        });
        changeTab(state, 0);
      }
    });
  } else if (state == 10) {
    formCompleted = true;
    window.location.href = redirectURL;
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

function checkEmployeeId(e) {
  $.ajax({
    url: empCheckUrl,
    type: "POST",
    data: {
      _token: CSRF,
      emp_id: e.value
    },
    dataType: 'json',
    cache: true,
    success: function success(res) {
      if (res) {
        EmpNotAlreadyExist = res.status;

        if (res.status) {
          IDEmpIdError.style.color = 'green';
        } else {
          IDEmpIdError.style.color = 'red';
        }

        IDEmpIdError.innerHTML = res.message;
      }
    }
  });
}

function checkPhoneEmail(e, For) {
  $.ajax({
    url: phoneEmailCheckURL,
    type: "POST",
    data: {
      _token: CSRF,
      id: e.value,
      "for": For
    },
    dataType: 'json',
    cache: true,
    success: function success(res) {
      if (For == 0) {
        PhoneNotAlreadyExist = res.status;

        if (res.status) {
          IDcontactError.style.color = 'green';
        } else {
          IDcontactError.style.color = 'red';
        }

        IDcontactError.innerHTML = res.message;
      } else if (For == 1) {
        EmailNotAlreadyExist = res.status;

        if (res.status) {
          IDemailError.style.color = 'green';
        } else {
          IDemailError.style.color = 'red';
        }

        IDemailError.innerHTML = res.message;
      }
    }
  });
}

var elem = 2;

function sameAddress(e) {
  $.ajax({
    url: stateCityURL,
    type: "POST",
    data: {
      _token: CSRF,
      value: IDtempCountry.value,
      state: 2,
      "for": 1
    },
    dataType: 'json',
    cache: true,
    success: function success(res) {
      if (res.request["for"] == 1) {
        IDpermanentState.innerHTML = '';
        IDpermanentCity.innerHTML = '';
        res.data.forEach(function (element) {
          var newOption = document.createElement('option');
          newOption.text = element.name;
          newOption.value = element.id;
          IDpermanentState.add(newOption);
        });

        if (parseInt(elem) == parseInt(e)) {
          IDpermanentState.value = IDtempState.value;
        }
      }
    }
  });
  $.ajax({
    url: stateCityURL,
    type: "POST",
    data: {
      _token: CSRF,
      value: IDtempState.value,
      state: 2,
      "for": 2
    },
    dataType: 'json',
    cache: true,
    success: function success(res) {
      if (res.request["for"] == 2) {
        IDpermanentCity.innerHTML = '';
        res.data.forEach(function (element) {
          var newOption = document.createElement('option');
          newOption.text = element.name;
          newOption.value = element.id;
          IDpermanentCity.add(newOption);
        });

        if (parseInt(elem) == parseInt(e)) {
          IDpermanentCity.value = IDtempCity.value;
        }
      }
    }
  });

  if (parseInt(elem) == parseInt(e)) {
    IDpermanentCountry.selectedIndex = 0;
    IDpermanentState.selectedIndex = 0;
    IDpermanentCity.selectedIndex = 0;
    IDpermanentPinCode.value = '';
    IDpermanentAddress.value = '';
    IDpermanentCountry.removeAttribute('readonly');
    IDpermanentState.removeAttribute('readonly');
    IDpermanentCity.removeAttribute('readonly');
    IDpermanentPinCode.removeAttribute('readonly');
    IDpermanentAddress.removeAttribute('readonly');
    elem = 2;
  } else {
    IDpermanentCountry.value = IDtempCountry.value; // IDpermanentState.value = IDtempState.value;
    // IDpermanentCity.value = IDtempCity.value;

    IDpermanentPinCode.value = IDtempPinCode.value;
    IDpermanentAddress.value = IDtempAddress.value;
    IDpermanentCountry.setAttribute('readonly', true);
    IDpermanentState.setAttribute('readonly', true);
    IDpermanentCity.setAttribute('readonly', true);
    IDpermanentPinCode.setAttribute('readonly', true);
    IDpermanentAddress.setAttribute('readonly', true);
    elem = e;
  }
}

function stateCity(value, state, For) {
  $.ajax({
    url: stateCityURL,
    type: "POST",
    data: {
      _token: CSRF,
      value: value,
      state: state,
      "for": For
    },
    dataType: 'json',
    cache: true,
    success: function success(res) {
      console.log(res);

      if (res.request.state == 1) {
        if (res.request["for"] == 1) {
          IDtempState.innerHTML = '';
          IDtempCity.innerHTML = ''; // var newOption = document.createElement('option');
          // newOption.text = 'Select State';
          // newOption.value = '';
          // IDtempState.add(newOption);

          res.data.forEach(function (element) {
            var newOption = document.createElement('option');
            newOption.text = element.name;
            newOption.value = element.id;
            IDtempState.add(newOption);
          });
        }

        if (res.request["for"] == 2) {
          IDtempCity.innerHTML = ''; // var newOption = document.createElement('option');
          // newOption.text = 'Select City';
          // newOption.value = element.id;
          // IDtempCity.add(newOption);

          res.data.forEach(function (element) {
            var newOption = document.createElement('option');
            newOption.text = element.name;
            newOption.value = element.id;
            IDtempCity.add(newOption);
          });
        }
      }

      if (res.request.state == 2) {
        if (res.request["for"] == 1) {
          IDpermanentState.innerHTML = '';
          IDpermanentCity.innerHTML = ''; // var newOption = document.createElement('option');
          // newOption.text = 'Select State';
          // newOption.value = element.id;
          // IDpermanentState.add(newOption);

          res.data.forEach(function (element) {
            var newOption = document.createElement('option');
            newOption.text = element.name;
            newOption.value = element.id;
            IDpermanentState.add(newOption);
          });
        }

        if (res.request["for"] == 2) {
          IDpermanentCity.innerHTML = ''; // var newOption = document.createElement('option');
          // newOption.text = 'Select City';
          // newOption.value = '';
          // IDpermanentCity.add(newOption);

          res.data.forEach(function (element) {
            var newOption = document.createElement('option');
            newOption.text = element.name;
            newOption.value = element.id;
            IDpermanentCity.add(newOption);
          });
        }
      }
    }
  });
}

function isValidEmail(email) {
  var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

function changeTab(action, current) {
  if (action == 0) {
    var current = 'tab' + current + 's';
    var current1 = 'tabBtn' + current;
    var goOn = 'tab' + action + 's';
    var goOn1 = 'tabBtn' + action;
    current.classList.add('d-none');
    current1.classList.add('d-none');
    goOn.classList.remove('d-none');
    goOn1.classList.remove('d-none');
  } else if (action == 1) {
    tab1s.classList.add('d-none');
    tabBtn1.classList.add('d-none');
    tabCard1.classList.add('d-none');
    tab2s.classList.remove('d-none');
    tabBtn2.classList.remove('d-none');
    tabCard2.classList.remove('d-none');
  } else if (action == 2) {
    tab2s.classList.add('d-none');
    tabBtn2.classList.add('d-none');
    tabCard2.classList.add('d-none');
    tab3s.classList.remove('d-none');
    tabBtn3.classList.remove('d-none');
    tabCard3.classList.remove('d-none');
  } else if (action == 3) {
    tab3s.classList.add('d-none');
    tabBtn3.classList.add('d-none');
    tabCard3.classList.add('d-none');
    tab4s.classList.remove('d-none');
    tabBtn4.classList.remove('d-none');
    tabCard4.classList.remove('d-none');
  } else if (action == 4) {
    tab4s.classList.add('d-none');
    tabBtn4.classList.add('d-none');
    tabCard4.classList.add('d-none');
    tab5s.classList.remove('d-none');
    tabBtn5.classList.remove('d-none');
    tabCard5.classList.remove('d-none');
  } else if (action == 5) {
    tab5s.classList.add('d-none');
    tabBtn5.classList.add('d-none');
    tabCard5.classList.add('d-none');
    tab6s.classList.remove('d-none');
    tabBtn6.classList.remove('d-none');
    tabCard6.classList.remove('d-none');
  } else if (action == 6) {
    tab6s.classList.add('d-none');
    tabBtn6.classList.add('d-none');
    tabCard6.classList.add('d-none');
    tab7s.classList.remove('d-none');
    tabBtn7.classList.remove('d-none');
    tabCard7.classList.remove('d-none');
  } else if (action == 7) {
    tab7s.classList.add('d-none');
    tabBtn7.classList.add('d-none');
    tabCard7.classList.add('d-none');
    tab8s.classList.remove('d-none');
    tabBtn8.classList.remove('d-none');
    tabCard8.classList.remove('d-none');
  } else if (action == 8) {
    tab8s.classList.add('d-none');
    tabBtn8.classList.add('d-none');
    tabCard8.classList.add('d-none');
    tab9s.classList.remove('d-none');
    tabBtn9.classList.remove('d-none');
    tabCard9.classList.remove('d-none');
  } else if (action == 9) {
    tab9s.classList.add('d-none');
    tabBtn9.classList.add('d-none');
    tab10s.classList.remove('d-none');
    tabBtn10.classList.remove('d-none');
    tabCard10.classList.remove('d-none');
  } else if (action == 10) {
    tab10s.classList.add('d-none');
    tabBtn10.classList.add('d-none');
    tabCard10.classList.add('d-none');
  }
}

function checkIFSC(ifsc) {
  $.ajax({
    url: ifscfURL,
    type: "POST",
    data: {
      _token: CSRF,
      ifsc: ifsc
    },
    dataType: 'json',
    cache: true,
    success: function success(res) {
      if (res.status) {
        console.log(res.data);
        IDbankName.value = res.data.BANK;
        IDbranchName.value = res.data.BRANCH + ', ' + res.data.ADDRESS;
        IDMICR.value = res.data.MICR;
        IDbranchCode.value = res.data.BANKCODE;
        bankNameElement.innerHTML = res.data.BANK;
        branchNameElement.innerHTML = res.data.BRANCH;
        micrElement.innerHTML = res.data.MICR;
        bankCodeElement.innerHTML = res.data.BANKCODE;
      }
    }
  });
}

function safeAssign(element, value) {
  var defaultValue = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '---';
  console.log(element.id, element.innerHTML);
  element.innerHTML = value == null || value == undefined || value == 0 || value == '' ? defaultValue : value;
}

function handleChange(event) {
  if (event.target.id == 'email') {
    EmailText.innerHTML = event.target.value;
  }

  console.log(event.target.id + " changed to: " + event.target.value);
  safeAssign(empIDText, IDemployee_id.value);
  safeAssign(contactText, IDcontact.value);
  safeAssign(birthdayText, IDdateOfBirth.value);
  safeAssign(genderText, IDgender.value);
  safeAssign(martialText, IDmariteStatus.value, '');
  safeAssign(bloodGroupText, IDbloodGroup.value, '');
  safeAssign(activeText, IDstatus.value);
  safeAssign(contractText, IDcontractType.value);
  safeAssign(joiningDateText, IDdateOfJoin.value);
  safeAssign(groupJoiningText, IDdateOfGroupJoin.value);
  safeAssign(gratuityDateText, IDdateOfGratuity.value);
  safeAssign(transferDateText, IDdateOfTransfer.value);
  safeAssign(expectedConfirmationText, IDdateOfExpectedConfirmation.value);
  safeAssign(probationDateText, IDprobationPeriod.value);
  safeAssign(confirmationDateText, IDdateOfConfirmation.value);
  safeAssign(payStructureDateText, IDdateOfPayStructure.value);
  safeAssign(esicLimitText, IDesic_limit.value, '');
  safeAssign(branchText, IDbranch.value);
  safeAssign(departmentText, IDdepartment.value);
  safeAssign(designationText, IDdesignation.value);
  safeAssign(assignMethodText, IDattendanceMethod.value);
  safeAssign(assignSetupText, IDasignSetup.value);
  safeAssign(shiftPolicyText, IDasignShift.value);
  safeAssign(assignShift, 'N/A');
  safeAssign(aadhardNumberText, IDaadhar_number.value);
  safeAssign(drivingLicenseText, IDdrivng_license_number.value);
  safeAssign(electionCardText, IDvoter_id_number.value);
  safeAssign(passportText, IDpassport_number.value);
  safeAssign(bankAcNumText, IDaccount_number.value);
  safeAssign(pftrustCode, IDpfTrustCode.value);
  safeAssign(pensionMember, IDpfFoundMember.value);
  safeAssign(pfNum, IDPfNumber.value);
  safeAssign(uniAcNum, IDuniversalAccountNumber.value);
  safeAssign(vpfPerc, IDvpfPercentage.value);
  safeAssign(pfJoinDate, IDpfDateOfJoining.value);
  safeAssign(pfLeaveDate, IDpfDateOfLeaving.value);
  safeAssign(PfLeaveReason, IDreasonOfLeavingPF.value);
  safeAssign(esicNum, IDesiNumber.value);
  safeAssign(esiDesperacy, IDesiDespensary.value);
  safeAssign(esicDateOfJoining, IDesiDateOfJoining.value);
  safeAssign(esicDateOfLeave, IDesiDateOfLeaving.value);
  safeAssign(esicLeaveReason, IDreasonOfLeavingESIC.value);
  safeAssign(qualification, IDqualification.value);
  safeAssign(stream, IDstream.value);
  safeAssign(courseType, IDcource_type.value);
  safeAssign(specialization, IDspecalization.value);
  safeAssign(natureOfCourse, IDcource_nature.value);
  safeAssign(qualificationStatus, IDquali_status.value);
  safeAssign(instituteName, IDinstitute_name.value);
  safeAssign(universityName, IDuniversityName.value);
  safeAssign(fromDate, IDedu_from_date.value);
  safeAssign(toDate, IDedu_to_date.value);
  safeAssign(passingDate, IDpassing_date.value);
  safeAssign(percentage, IDpercentage.value);
  safeAssign(grade, IDgrade.value);
  safeAssign(durationOfCourse, IDduration.value);
  safeAssign(year, IDyear.value);
  safeAssign(yearOfServiceElement, IDyearOfService.value);
  safeAssign(retirementDateElement, IDretirementDate.value);
  safeAssign(separationSubmitOnElement, IDseparationSubmitDate.value);
  safeAssign(expectedLeavingDateElement, IDexpectedLeavingDate.value);
  safeAssign(leavingDatePeriodElement, IDleavingDateAsPerNoticePeriod.value);
  safeAssign(noticePeriodDaysElement, IDnoticePeriodRequiredDate.value);
  safeAssign(reasonForLeavingElement, IDreasonForLeave.value);
  safeAssign(leavingDateElement, IDleaveDate.value);
  safeAssign(noticeServedDaysElement, IDnoticePeriodServedDate.value);
  safeAssign(settlementFromElement, IDsettlementFrom.value);
  safeAssign(finalSettlementDateElement, IDfinalSettlementDate.value);
  safeAssign(noticePeriodShortfallDaysElement, IDnoticePeriodShortfallDays.value);
  safeAssign(exitInterviewDateElement, IDexitInterviewDate.value);
  safeAssign(lastWorkingDateElement, IDlastWorkingDate.value);
  safeAssign(remarkElement, IDremark.value);
  safeAssign(noticePeriodForEmployerElement, IDemployerNoticePeriod.value);
  safeAssign(noticePeriodForEmployeeElement, IDemployeeNoticePeriod.value);
  safeAssign(ifscElement, IDifsc.value);
  safeAssign(bankNameElement, IDbankName.value); // safeAssign(bankAccountNumberElement, IDbank_account_number.value);
}

IDfirstName.addEventListener('change', handleChange);
IDmiddleName.addEventListener('change', handleChange);
IDlastName.addEventListener('change', handleChange);
IDemail.addEventListener('change', handleChange);
IDtempSameAsParmanant.addEventListener('change', handleChange);
IDemployee_id.addEventListener('change', handleChange);
IDcontact.addEventListener('change', handleChange);
IDdateOfBirth.addEventListener('change', handleChange);
IDgender.addEventListener('change', handleChange);
IDmariteStatus.addEventListener('change', handleChange);
IDbloodGroup.addEventListener('change', handleChange);
IDstatus.addEventListener('change', handleChange);
IDcontractType.addEventListener('change', handleChange);
IDdateOfJoin.addEventListener('change', handleChange);
IDdateOfGroupJoin.addEventListener('change', handleChange);
IDdateOfGratuity.addEventListener('change', handleChange);
IDdateOfTransfer.addEventListener('change', handleChange);
IDdateOfExpectedConfirmation.addEventListener('change', handleChange);
IDprobationPeriod.addEventListener('change', handleChange);
IDdateOfConfirmation.addEventListener('change', handleChange);
IDdateOfPayStructure.addEventListener('change', handleChange);
IDesic_limit.addEventListener('change', handleChange);
IDbranch.addEventListener('change', handleChange);
IDdepartment.addEventListener('change', handleChange);
IDdesignation.addEventListener('change', handleChange);
IDattendanceMethod.addEventListener('change', handleChange);
IDasignSetup.addEventListener('change', handleChange);
IDasignShift.addEventListener('change', handleChange);
IDaadhar_number.addEventListener('change', handleChange);
IDupload_aadhar.addEventListener('change', handleChange);
IDdrivng_license_number.addEventListener('change', handleChange);
IDupload_drivng_license.addEventListener('change', handleChange);
IDvoter_id_number.addEventListener('change', handleChange);
IDupload_voter_id.addEventListener('change', handleChange);
IDpassport_number.addEventListener('change', handleChange);
IDupload_passport.addEventListener('change', handleChange);
IDaccount_number.addEventListener('change', handleChange);
IDupload_passbook.addEventListener('change', handleChange);
IDpfTrustCode.addEventListener('change', handleChange);
IDpfFoundMember.addEventListener('change', handleChange);
IDPfNumber.addEventListener('change', handleChange);
IDuniversalAccountNumber.addEventListener('change', handleChange);
IDvpfPercentage.addEventListener('change', handleChange);
IDpfDateOfJoining.addEventListener('change', handleChange);
IDpfDateOfLeaving.addEventListener('change', handleChange);
IDreasonOfLeavingPF.addEventListener('change', handleChange);
IDesiNumber.addEventListener('change', handleChange);
IDesiDespensary.addEventListener('change', handleChange);
IDesiDateOfJoining.addEventListener('change', handleChange);
IDesiDateOfLeaving.addEventListener('change', handleChange);
IDreasonOfLeavingESIC.addEventListener('change', handleChange);
IDqualification.addEventListener('change', handleChange);
IDstream.addEventListener('change', handleChange);
IDcource_type.addEventListener('change', handleChange);
IDspecalization.addEventListener('change', handleChange);
IDcource_nature.addEventListener('change', handleChange);
IDquali_status.addEventListener('change', handleChange);
IDinstitute_name.addEventListener('change', handleChange);
IDuniversityName.addEventListener('change', handleChange);
IDedu_from_date.addEventListener('change', handleChange);
IDedu_to_date.addEventListener('change', handleChange);
IDpassing_date.addEventListener('change', handleChange);
IDpercentage.addEventListener('change', handleChange);
IDgrade.addEventListener('change', handleChange);
IDduration.addEventListener('change', handleChange);
IDyear.addEventListener('change', handleChange);
IDtempCountry.addEventListener('change', handleChange);
IDtempState.addEventListener('change', handleChange);
IDtempCity.addEventListener('change', handleChange);
IDtempPinCode.addEventListener('change', handleChange);
IDtempAddress.addEventListener('change', handleChange);
IDpermanentCountry.addEventListener('change', handleChange);
IDpermanentState.addEventListener('change', handleChange);
IDpermanentCity.addEventListener('change', handleChange);
IDpermanentPinCode.addEventListener('change', handleChange);
IDpermanentAddress.addEventListener('change', handleChange);
IDyearOfService.addEventListener('change', handleChange);
IDretirementDate.addEventListener('change', handleChange);
IDseparationSubmitDate.addEventListener('change', handleChange);
IDexpectedLeavingDate.addEventListener('change', handleChange);
IDleavingDateAsPerNoticePeriod.addEventListener('change', handleChange);
IDnoticePeriodRequiredDate.addEventListener('change', handleChange);
IDreasonForLeave.addEventListener('change', handleChange);
IDleaveDate.addEventListener('change', handleChange);
IDnoticePeriodServedDate.addEventListener('change', handleChange);
IDsettlementFrom.addEventListener('change', handleChange);
IDfinalSettlementDate.addEventListener('change', handleChange);
IDnoticePeriodShortfallDays.addEventListener('change', handleChange);
IDexitInterviewDate.addEventListener('change', handleChange);
IDlastWorkingDate.addEventListener('change', handleChange);
IDremark.addEventListener('change', handleChange);
IDemployerNoticePeriod.addEventListener('change', handleChange);
IDemployeeNoticePeriod.addEventListener('change', handleChange);
IDifsc.addEventListener('change', handleChange);
IDbankName.addEventListener('change', handleChange);
IDbranchName.addEventListener('change', handleChange);
IDbank_account_number.addEventListener('change', handleChange);
IDMICR.addEventListener('change', handleChange);
IDbranchCode.addEventListener('change', handleChange);
var fileInput = document.getElementById('profileInput');
var avtarEmp = document.getElementById('empAvtar');

function inputClick(e) {
  fileInput.click();
}

;

function profileSet() {
  if (IDemployee_id.value && EmpNotAlreadyExist && EmailNotAlreadyExist && PhoneNotAlreadyExist) {
    var file = fileInput.files[0];

    if (file) {
      var reader = new FileReader();

      reader.onload = function (e) {
        console.log(e);
        avtarEmp.style.backgroundImage = 'url(' + e.target.result + ')';
      };

      reader.readAsDataURL(file);
      uploadFile();
    }
  } else {
    Swal.fire({
      icon: 'error',
      text: 'Enter Employee Id.',
      timer: 3000,
    });
  }
}

function uploadFile() {
  var file = fileInput.files[0];
  var formData = new FormData();
  formData.append('avatar', file);
  formData.append('_token', CSRF);
  formData.append('emp_id', IDemployee_id.value);
  $.ajax({
    url: profileURL,
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    dataType: 'json',
    cache: false,
    success: function success(res) {
      profilePath = res.path;
      Swal.fire({
        icon: 'success',
        text: 'Photo saved successfully.',
        timer: 3000,
      });
    },
    error: function error(xhr, status, _error) {
      Swal.fire({
        icon: 'error',
        text: 'Data save failed.',
        timer: 3000,
      });
    }
  });
}

function uploadDocs() {
  var file_aadhar = IDupload_aadhar.files[0];
  var file_drivng_license = IDupload_drivng_license.files[0];
  var file_voter_id = IDupload_voter_id.files[0];
  var file_passport = IDupload_passport.files[0];
  var file_passbook = IDupload_passbook.files[0];
  var formData = new FormData();
  formData.append('_token', CSRF);
  formData.append('id', 5);
  formData.append('aadharUpload', file_aadhar);
  formData.append('drivingUpload', file_drivng_license);
  formData.append('voterUpload', file_voter_id);
  formData.append('passportUpload', file_passport);
  formData.append('passbookUpload', file_passbook);
  formData.append('emp_id', IDemployee_id.value);
  formData.append('aadhar_number', IDaadhar_number.value);
  formData.append('drivng_license_number', IDdrivng_license_number.value);
  formData.append('voter_id_number', IDvoter_id_number.value);
  formData.append('passport_number', passport_number.value);
  formData.append('account_number', IDaccount_number.value);
  $.ajax({
    url: documentURL,
    type: "POST",
    data: formData,
    dataType: 'json',
    cache: true,
    success: function success(res) {
      console.log(res); // changeTab(state, 0);
    }
  });
}
