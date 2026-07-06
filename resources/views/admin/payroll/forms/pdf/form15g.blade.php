<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FORM NO. 15G</title>
    <style>
        @page { margin: 11mm 9mm; size: A4 portrait; }
        body { font-family: "Times New Roman", Times, serif; font-size: 10pt; line-height: 1.14; color: #111; }
        .rule-title { background: #d9d9db; text-align: center; font-size: 12pt; letter-spacing: 0.2px; padding: 4px 0; margin-bottom: 8px; }
        .center { text-align: center; }
        .form-head { font-weight: 700; font-size: 11pt; margin: 0; }
        .sub-head { font-size: 9pt; margin-top: 2px; font-style: italic; }
        .desc { font-size: 11pt; font-weight: 700; line-height: 1.2; margin-top: 5px; }
        .part-label { text-align: center; font-size: 11pt; font-weight: 700; margin: 8px 0 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        td, th { border: 1px solid #333; padding: 3px 5px; vertical-align: top; font-size: 9.2pt; font-weight: 400; }
        .num { width: 6%; }
        .yesno-box { display: inline-block; width: 18px; height: 12px; border: 1px solid #333; vertical-align: middle; margin-left: 4px; }
        .blank-row td { height: 15px; }
        .sig-line { text-align: right; margin-top: 8px; font-size: 11pt; }
        .verify-title { text-align: center; font-size: 11.5pt; font-style: italic; font-weight: 700; margin-top: 8px; }
        .verify { margin-top: 4px; text-align: justify; font-size: 10pt; line-height: 1.2; }
        .sign-block { margin-top: 14px; width: 100%; }
        .sign-left, .sign-right { display: inline-block; width: 49%; vertical-align: top; font-size: 10pt; }
        .sign-right { text-align: right; }
        .note { margin-top: 12px; border-top: 1px solid #333; padding-top: 7px; font-size: 9pt; line-height: 1.16; }
        .page-break { page-break-before: always; }
        .part2-wrap { margin-top: 40px; }
        .part2-title { text-align: center; font-size: 11pt; font-weight: 700; }
        .part2-subtitle { text-align: center; font-size: 11pt; font-weight: 700; line-height: 1.18; margin-top: 3px; }
        .part2-table td { font-size: 9.2pt; }
        .part2-sign { margin-top: 14px; width: 100%; }
        .part2-sign .l, .part2-sign .r { display: inline-block; width: 49%; vertical-align: top; font-size: 10pt; }
        .part2-sign .r { text-align: right; }
        .notes-block { margin-top: 12px; font-size: 9pt; line-height: 1.16; text-align: left; }
        .notes-block p { margin: 0 0 2px 0; }
    </style>
</head>
<body>
    <div class="rule-title">INCOME-TAX RULES, 1962</div>

    <div class="center form-head">FORM NO. 15G</div>
    <div class="center sub-head">[See section 197A(1), 197A(1A) and rule 29C]</div>
    <div class="center desc">
        Declaration under section 197A(1) and section 197A(1A) to be made by an<br>
        individual or a person (not being a company or firm) claiming certain<br>
        incomes without deduction of tax
    </div>
    <div class="part-label">PART I</div>

    <table>
        <tr>
            <td colspan="5">1. Name of Assessee (Declarant) <span style="float:right">{{ $employee->name ?? '' }}</span></td>
            <td colspan="5">2. PAN of the Assessee <span style="float:right">{{ $employee->pan ?? '' }}</span></td>
        </tr>
        <tr>
            <td colspan="2">3. Status <span style="float:right">{{ $employee->status ?? 'Individual' }}</span></td>
            <td colspan="4">4. Previous year(P.Y.)<br>(for which declaration is being made) <span style="float:right">{{ $previousYear ?? '' }}</span></td>
            <td colspan="4">5. Residential Status <span style="float:right">{{ $employee->residential_status ?? 'Resident' }}</span></td>
        </tr>
        <tr>
            <td colspan="3">6. Flat/ Door/ Block No. <span style="float:right">-</span></td>
            <td colspan="3">7. Name of Premises <span style="float:right">-</span></td>
            <td colspan="2">8. Road/Street /Lane <span style="float:right">-</span></td>
            <td colspan="2">9. Area/ Locality <span style="float:right">-</span></td>
        </tr>
        <tr>
            <td colspan="2">10. Town/City /District <span style="float:right">{{ $company->address ?? '-' }}</span></td>
            <td colspan="2">11. State <span style="float:right">-</span></td>
            <td colspan="2">12. PIN <span style="float:right">-</span></td>
            <td colspan="4">13. Email <span style="float:right">{{ $employee->email ?? '' }}</span></td>
        </tr>
        <tr>
            <td colspan="3">14. Telephone No. (with STD<br>Code) and Mobile No. <span style="float:right">{{ $employee->mobile ?? '' }}</span></td>
            <td colspan="7">
                15 (a) Whether assessed to tax under the<br>Income-tax Act, 1961 :
                <span style="margin-left:16px;">Yes</span><span class="yesno-box"></span>
                <span style="margin-left:16px;">No</span><span class="yesno-box"></span><br>
                (b) If yes, latest assessment year for which assessed
                <span style="float:right">{{ $assessmentYear ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="5">16. Estimated income for which this declaration is made <span style="float:right">{{ number_format($estimatedIncome ?? 0, 2) }}</span></td>
            <td colspan="5">17. Estimated total income of the P.Y. in which<br>income mentioned in column 16 to be included <span style="float:right">{{ number_format($estimatedIncome ?? 0, 2) }}</span></td>
        </tr>
        <tr>
            <td colspan="10">18. Details of Form No. 15G other than this form filed during the previous year, if any</td>
        </tr>
        <tr>
            <td colspan="4" class="center">Total No. of Form No. 15G filed</td>
            <td colspan="6" class="center">Aggregate amount of income for which Form No.15G filed</td>
        </tr>
        <tr class="blank-row">
            <td colspan="4"></td>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td colspan="10">19. Details of income for which the declaration is filed</td>
        </tr>
        <tr>
            <td class="num">Sl.<br>No.</td>
            <td colspan="3">Identification number of relevant<br>investment/account, etc.</td>
            <td colspan="2">Nature of income</td>
            <td colspan="2">Section under which tax<br>is deductible</td>
            <td colspan="2">Amount of income</td>
        </tr>
        <tr class="blank-row">
            <td></td><td colspan="3"></td><td colspan="2"></td><td colspan="2"></td><td colspan="2"></td>
        </tr>
    </table>

    <div class="sig-line">........................................................<br><em>Signature of the Declarant</em></div>

    <div class="verify-title">Declaration/Verification</div>
    <div class="verify">
        *I/We................................................................ do hereby declare that to the best of *my/our knowledge and belief what is
        stated above is correct, complete and is truly stated. *I/We declare that the incomes referred to in this form are not includible in the total
        income of any other person under sections 60 to 64 of the Income-tax Act, 1961. *I/We further declare that the tax on my/our estimated total
        income including *income/incomes referred to in column 16 and aggregate amount of *income/incomes referred in column 18 computed in accordance
        with the provisions of the Income-tax Act, 1961, for the previous year ending on ........................ relevant to the assessment year
        .................... will be nil. *I/We also declare that *my/our *income/incomes referred to in column 16 and the aggregate amount of
        *income/incomes referred to in column 18 for the previous year ending on ........................ relevant to the assessment year
        ........................ will not exceed the maximum amount which is not chargeable to income-tax.
    </div>

    <div class="sign-block">
        <div class="sign-left">
            <em>Place:</em> ..............................<br>
            <em>Date:</em> ................................
        </div>
        <div class="sign-right">
            ........................................................<br>
            <em>Signature of the Declarant</em>
        </div>
    </div>

    <div class="note">
        1. Substituted by IT (Fourteenth Amdt.) Rules 2015, w.e.f. <strong>1-10-2015</strong>. Earlier Form No. 15G was inserted by the IT (Fifth Amdt.)
        Rules, 1982, w.e.f. 21-6-1982 and later on amended by the IT (Fifth Amdt.) Rules, 1989, w.e.f. 1-4-1988, IT (Fourteenth Amdt.) Rules, 1990,
        w.e.f. 20-11-1990 and IT (Twelfth Amdt.) Rules, 2002, w.e.f. 21-6-2002 and substituted by the IT (Eighth Amdt.) Rules, 2003, w.e.f. 9-6-2003
        and IT (Second Amdt.) Rules, 2013, w.e.f. 19-2-2013.
    </div>

    <div class="page-break"></div>

    <div class="part2-wrap">
        <div class="part2-title">PART II</div>
        <div class="part2-subtitle">[To be filled by the person responsible for paying the income<br>referred to in column 16 of Part I]</div>

        <table class="part2-table" style="margin-top:10px;">
            <tr>
                <td colspan="5">1. Name of the person responsible for paying <span style="float:right">{{ $company->name ?? '' }}</span></td>
                <td colspan="5">2. Unique Identification No.<sup>11</sup> <span style="float:right">-</span></td>
            </tr>
            <tr>
                <td colspan="2">3. PAN of the person<br>responsible for paying <span style="float:right">{{ $company->pan ?? '' }}</span></td>
                <td colspan="4">4. Complete Address <span style="float:right">{{ $company->address ?? '' }}</span></td>
                <td colspan="4">5. TAN of the person responsible for paying <span style="float:right">{{ $company->tan ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="2">6. Email <span style="float:right">{{ $company->email ?? '' }}</span></td>
                <td colspan="4">7. Telephone No. (with STD Code) and Mobile No. <span style="float:right">{{ $company->phone ?? '' }}</span></td>
                <td colspan="4">8. Amount of income paid<sup>12</sup> <span style="float:right">{{ number_format($estimatedIncome ?? 0, 2) }}</span></td>
            </tr>
            <tr>
                <td colspan="5">9. Date on which Declaration is received<br>(DD/MM/YYYY) <span style="float:right">{{ $currentDate ?? '' }}</span></td>
                <td colspan="5">10. Date on which the income has been paid/ credited<br>(DD/MM/YYYY) <span style="float:right">{{ $currentDate ?? '' }}</span></td>
            </tr>
            <tr class="blank-row">
                <td colspan="5"></td>
                <td colspan="5"></td>
            </tr>
        </table>

        <div class="part2-sign">
            <div class="l">
                <em>Place:</em> ...................................<br>
                <em>Date:</em> ....................................
            </div>
            <div class="r">
                ........................................................................<br>
                <em>Signature of the person responsible for paying</em><br>
                <em>the income referred to in column 16 of Part I</em>
            </div>
        </div>

        <div class="notes-block">
            <p>*Delete whichever is not applicable.</p>
            <p><sup>1</sup>As per provisions of section 206AA(2), the declaration under section 197A(1) or 197A(1A) shall be invalid if the declarant fails to furnish his valid Permanent Account Number (PAN).</p>
            <p><sup>2</sup>Declaration can be furnished by an individual under section 197A(1) and a person (other than a company or a firm) under section 197A(1A).</p>
            <p><sup>3</sup>The financial year to which the income pertains.</p>
            <p><sup>4</sup>Please mention the residential status as per the provisions of section 6 of the Income-tax Act, 1961.</p>
            <p><sup>5</sup>Please mention "Yes" if assessed to tax under the provisions of Income-tax Act, 1961 for any of the assessment year out of six assessment years preceding the year in which the declaration is filed.</p>
            <p><sup>6</sup>Please mention the amount of estimated total income of the previous year for which the declaration is filed including the amount of income for which this declaration is made.</p>
            <p><sup>7</sup>In case any declaration(s) in Form No. 15G is filed before filing this declaration during the previous year, mention the total number of such Form No. 15G filed along with the aggregate amount of income for which said declaration(s) have been filed.</p>
            <p><sup>8</sup>Mention the distinctive number of shares, account number of term deposit, recurring deposit, National Savings Schemes, life insurance policy number, employee code, etc.</p>
            <p><sup>9</sup>Indicate the capacity in which the declaration is furnished on behalf of a HUF, AOP, etc.</p>
            <p><sup>10</sup>Before signing the declaration/verification, the declarant should satisfy himself that the information furnished in this form is true, correct and complete in all respects. Any person making a false statement in the declaration shall be liable to prosecution under section 277 of the Income-tax Act, 1961 and on conviction be punishable-</p>
            <p style="padding-left:18px;">(i)&nbsp;&nbsp;in a case where tax sought to be evaded exceeds twenty-five lakh rupees, with rigorous imprisonment which shall not be less than six months but which may extend to seven years and with fine;</p>
            <p style="padding-left:18px;">(ii)&nbsp;in any other case, with rigorous imprisonment which shall not be less than three months but which may extend to two years and with fine.</p>
        </div>
    </div>

    <div class="page-break"></div>

    <div class="notes-block" style="margin-top:0;">
        <p><sup>11</sup>The person responsible for paying the income referred to in column 16 of Part I shall allot a unique identification number to all the Form No. 15G received by him during a quarter of the financial year and report this reference number along with the particulars prescribed in rule 31A(4)(vii) of the Income-tax Rules, 1962 in the TDS statement furnished for the same quarter. In case the person has also received Form No.15H during the same quarter, please allot separate series of serial number for Form No.15G and Form No.15H.</p>
        <p><sup>12</sup>The person responsible for paying the income referred to in column 16 of Part I shall not accept the declaration where the amount of income of the nature referred to in sub-section (1) or sub-section (1A) of section 197A or the aggregate of the amounts of such income credited or paid or likely to be credited or paid during the previous year in which such income is to be included exceeds the maximum amount which is not chargeable to tax. For deciding the eligibility, he is required to verify income or the aggregate amount of incomes, as the case may be, reported by the declarant in columns 16 and 18.</p>
    </div>
</body>
</html>
