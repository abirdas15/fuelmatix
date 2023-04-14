<template>
    <div class="content-body">
        <div class="container-fluid">
            <div class="popup-wrapper">
                <div class="popup">
                    <ul>
                        <li><a href="#">New SubAccount</a></li>
                        <li><a href="#">Rename</a></li>
                        <li><a href="#">New account</a></li>
                        <li><a href="#">Edit Account</a></li>
                        <li><a href="#" id="newAccount">New account</a></li>
                        <li><a href="#">Delete account</a></li>
                    </ul>
                </div>
            </div>

            <div class="new-account-form-wrapper">
                <form>
                    <div class="input-wrapper">
                        <label for="accName">Account name</label>
                        <input type="text" id="accName"/>
                    </div>
                    <div class="input-wrapper">
                        <label for="accCode">Account code</label>
                        <input type="text" id="accCode"/>
                    </div>
                    <div class="input-wrapper">
                        <label for="desc" class="desc-label">Description</label>
                        <textarea name="" id="desc" cols="30" rows="7"></textarea>
                    </div>
                    <div class="input-wrapper">
                        <label for="country">Set country</label>
                        <select id="country">
                            <option value="  " selected>Select a country</option>
                            <option value="--">Not Specified</option>
                            <option value="AF">Afghanistan</option>
                            <option value="AL">Albania</option>
                            <option value="DZ">Algeria</option>
                            <option value="AS">American Samoa</option>
                            <option value="AD">Andorra</option>
                            <option value="AO">Angola</option>
                            <option value="AI">Anguilla</option>
                            <option value="AQ">Antarctica</option>
                            <option value="AG">Antigua and Barbuda</option>
                            <option value="AR">Argentina</option>
                            <option value="AM">Armenia</option>
                            <option value="AW">Aruba</option>
                            <option value="AU">Australia</option>
                            <option value="AT">Austria</option>
                            <option value="AZ">Azerbaijan</option>
                            <option value="BS">Bahamas</option>
                            <option value="BH">Bahrain</option>
                            <option value="BD">Bangladesh</option>
                            <option value="BB">Barbados</option>
                            <option value="BY">Belarus</option>
                            <option value="BE">Belgium</option>
                            <option value="BZ">Belize</option>
                            <option value="BJ">Benin</option>
                            <option value="BM">Bermuda</option>
                            <option value="BT">Bhutan</option>
                            <option value="BO">Bolivia</option>
                            <option value="BA">Bosnia and Herzegowina</option>
                            <option value="BW">Botswana</option>
                            <option value="BV">Bouvet Island</option>
                            <option value="BR">Brazil</option>
                            <option value="IO">British Indian Ocean Territory</option>
                            <option value="BN">Brunei Darussalam</option>
                            <option value="BG">Bulgaria</option>
                            <option value="BF">Burkina Faso</option>
                            <option value="BI">Burundi</option>
                            <option value="KH">Cambodia</option>
                            <option value="CM">Cameroon</option>
                            <option value="CA">Canada</option>
                            <option value="CV">Cape Verde</option>
                            <option value="KY">Cayman Islands</option>
                            <option value="CF">Central African Republic</option>
                            <option value="TD">Chad</option>
                            <option value="CL">Chile</option>
                            <option value="CN">China</option>
                            <option value="CX">Christmas Island</option>
                            <option value="CC">Cocos (Keeling) Islands</option>
                            <option value="CO">Colombia</option>
                            <option value="KM">Comoros</option>
                            <option value="CG">Congo</option>
                            <option value="CD">Congo, the Democratic Republic of the</option>
                            <option value="CK">Cook Islands</option>
                            <option value="CR">Costa Rica</option>
                            <option value="CI">Cote d'Ivoire</option>
                            <option value="HR">Croatia (Hrvatska)</option>
                            <option value="CU">Cuba</option>
                            <option value="CY">Cyprus</option>
                            <option value="CZ">Czech Republic</option>
                            <option value="DK">Denmark</option>
                            <option value="DJ">Djibouti</option>
                            <option value="DM">Dominica</option>
                            <option value="DO">Dominican Republic</option>
                            <option value="TP">East Timor</option>
                            <option value="EC">Ecuador</option>
                            <option value="EG">Egypt</option>
                            <option value="SV">El Salvador</option>
                            <option value="GQ">Equatorial Guinea</option>
                            <option value="ER">Eritrea</option>
                            <option value="EE">Estonia</option>
                            <option value="ET">Ethiopia</option>
                            <option value="FK">Falkland Islands (Malvinas)</option>
                            <option value="FO">Faroe Islands</option>
                            <option value="FJ">Fiji</option>
                            <option value="FI">Finland</option>
                            <option value="FR">France</option>
                            <option value="FX">France, Metropolitan</option>
                            <option value="GF">French Guiana</option>
                            <option value="PF">French Polynesia</option>
                            <option value="TF">French Southern Territories</option>
                            <option value="GA">Gabon</option>
                            <option value="GM">Gambia</option>
                            <option value="GE">Georgia</option>
                            <option value="DE">Germany</option>
                            <option value="GH">Ghana</option>
                            <option value="GI">Gibraltar</option>
                            <option value="GR">Greece</option>
                            <option value="GL">Greenland</option>
                            <option value="GD">Grenada</option>
                            <option value="GP">Guadeloupe</option>
                            <option value="GU">Guam</option>
                            <option value="GT">Guatemala</option>
                            <option value="GN">Guinea</option>
                            <option value="GW">Guinea-Bissau</option>
                            <option value="GY">Guyana</option>
                            <option value="HT">Haiti</option>
                            <option value="HM">Heard and Mc Donald Islands</option>
                            <option value="VA">Holy See (Vatican City State)</option>
                            <option value="HN">Honduras</option>
                            <option value="HK">Hong Kong</option>
                            <option value="HU">Hungary</option>
                            <option value="IS">Iceland</option>
                            <option value="IN">India</option>
                            <option value="ID">Indonesia</option>
                            <option value="IR">Iran (Islamic Republic of)</option>
                            <option value="IQ">Iraq</option>
                            <option value="IE">Ireland</option>
                            <option value="IL">Israel</option>
                            <option value="IT">Italy</option>
                            <option value="JM">Jamaica</option>
                            <option value="JP">Japan</option>
                            <option value="JO">Jordan</option>
                            <option value="KZ">Kazakhstan</option>
                            <option value="KE">Kenya</option>
                            <option value="KI">Kiribati</option>
                            <option value="KP">Korea, Democratic People's Republic of</option>
                            <option value="KR">Korea, Republic of</option>
                            <option value="KW">Kuwait</option>
                            <option value="KG">Kyrgyzstan</option>
                            <option value="LA">Lao People's Democratic Republic</option>
                            <option value="LV">Latvia</option>
                            <option value="LB">Lebanon</option>
                            <option value="LS">Lesotho</option>
                            <option value="LR">Liberia</option>
                            <option value="LY">Libyan Arab Jamahiriya</option>
                            <option value="LI">Liechtenstein</option>
                            <option value="LT">Lithuania</option>
                            <option value="LU">Luxembourg</option>
                            <option value="MO">Macau</option>
                            <option value="MK">Macedonia, The Former Yugoslav Republic of</option>
                            <option value="MG">Madagascar</option>
                            <option value="MW">Malawi</option>
                            <option value="MY">Malaysia</option>
                            <option value="MV">Maldives</option>
                            <option value="ML">Mali</option>
                            <option value="MT">Malta</option>
                            <option value="MH">Marshall Islands</option>
                            <option value="MQ">Martinique</option>
                            <option value="MR">Mauritania</option>
                            <option value="MU">Mauritius</option>
                            <option value="YT">Mayotte</option>
                            <option value="MX">Mexico</option>
                            <option value="FM">Micronesia, Federated States of</option>
                            <option value="MD">Moldova, Republic of</option>
                            <option value="MC">Monaco</option>
                            <option value="MN">Mongolia</option>
                            <option value="MS">Montserrat</option>
                            <option value="MA">Morocco</option>
                            <option value="MZ">Mozambique</option>
                            <option value="MM">Myanmar</option>
                            <option value="NA">Namibia</option>
                            <option value="NR">Nauru</option>
                            <option value="NP">Nepal</option>
                            <option value="NL">Netherlands</option>
                            <option value="AN">Netherlands Antilles</option>
                            <option value="NC">New Caledonia</option>
                            <option value="NZ">New Zealand</option>
                            <option value="NI">Nicaragua</option>
                            <option value="NE">Niger</option>
                            <option value="NG">Nigeria</option>
                            <option value="NU">Niue</option>
                            <option value="NF">Norfolk Island</option>
                            <option value="MP">Northern Mariana Islands</option>
                            <option value="NO">Norway</option>
                            <option value="OM">Oman</option>
                            <option value="PK">Pakistan</option>
                            <option value="PW">Palau</option>
                            <option value="PA">Panama</option>
                            <option value="PG">Papua New Guinea</option>
                            <option value="PY">Paraguay</option>
                            <option value="PE">Peru</option>
                            <option value="PH">Philippines</option>
                            <option value="PN">Pitcairn</option>
                            <option value="PL">Poland</option>
                            <option value="PT">Portugal</option>
                            <option value="PR">Puerto Rico</option>
                            <option value="QA">Qatar</option>
                            <option value="RE">Reunion</option>
                            <option value="RO">Romania</option>
                            <option value="RU">Russian Federation</option>
                            <option value="RW">Rwanda</option>
                            <option value="KN">Saint Kitts and Nevis</option>
                            <option value="LC">Saint LUCIA</option>
                            <option value="VC">Saint Vincent and the Grenadines</option>
                            <option value="WS">Samoa</option>
                            <option value="SM">San Marino</option>
                            <option value="ST">Sao Tome and Principe</option>
                            <option value="SA">Saudi Arabia</option>
                            <option value="SN">Senegal</option>
                            <option value="SC">Seychelles</option>
                            <option value="SL">Sierra Leone</option>
                            <option value="SG">Singapore</option>
                            <option value="SK">Slovakia (Slovak Republic)</option>
                            <option value="SI">Slovenia</option>
                            <option value="SB">Solomon Islands</option>
                            <option value="SO">Somalia</option>
                            <option value="ZA">South Africa</option>
                            <option value="GS">South Georgia and the South Sandwich Islands</option>
                            <option value="ES">Spain</option>
                            <option value="LK">Sri Lanka</option>
                            <option value="SH">St. Helena</option>
                            <option value="PM">St. Pierre and Miquelon</option>
                            <option value="SD">Sudan</option>
                            <option value="SR">Suriname</option>
                            <option value="SJ">Svalbard and Jan Mayen Islands</option>
                            <option value="SZ">Swaziland</option>
                            <option value="SE">Sweden</option>
                            <option value="CH">Switzerland</option>
                            <option value="SY">Syrian Arab Republic</option>
                            <option value="TW">Taiwan, Province of China</option>
                            <option value="TJ">Tajikistan</option>
                            <option value="TZ">Tanzania, United Republic of</option>
                            <option value="TH">Thailand</option>
                            <option value="TG">Togo</option>
                            <option value="TK">Tokelau</option>
                            <option value="TO">Tonga</option>
                            <option value="TT">Trinidad and Tobago</option>
                            <option value="TN">Tunisia</option>
                            <option value="TR">Turkey</option>
                            <option value="TM">Turkmenistan</option>
                            <option value="TC">Turks and Caicos Islands</option>
                            <option value="TV">Tuvalu</option>
                            <option value="UG">Uganda</option>
                            <option value="UA">Ukraine</option>
                            <option value="AE">United Arab Emirates</option>
                            <option value="GB">United Kingdom</option>
                            <option value="US">United States</option>
                            <option value="UM">United States Minor Outlying Islands</option>
                            <option value="UY">Uruguay</option>
                            <option value="UZ">Uzbekistan</option>
                            <option value="VU">Vanuatu</option>
                            <option value="VE">Venezuela</option>
                            <option value="VN">Viet Nam</option>
                            <option value="VG">Virgin Islands (British)</option>
                            <option value="VI">Virgin Islands (U.S.)</option>
                            <option value="WF">Wallis and Futuna Islands</option>
                            <option value="EH">Western Sahara</option>
                            <option value="YE">Yemen</option>
                            <option value="YU">Yugoslavia</option>
                            <option value="ZM">Zambia</option>
                            <option value="ZW">Zimbabwe</option>
                        </select>
                    </div>
                    <div class="input-wrapper">
                        <label for="notes" class="notes-label">Notes</label>
                        <textarea name="" id="notes" cols="30" rows="7"></textarea>
                    </div>
                    <div class="btn-wrapper">
                        <button type="submit">Ok</button>
                        <button type="reset" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>

            <ul class="accordion-wrapper">
                <li class="accordion-heading-wrapper">
                    <h4>Account name</h4>
                    <h4>Description</h4>
                    <h4>Total</h4>
                </li>
                <li v-for="heads in accHeads">
                    <a href="#" @contextmenu="rightClick($event)" class="accordion-btn"><span><img
                        src="images/arrow-svg.svg" alt=""/>
                        Assets</span><span>Assets</span><span>8364983</span></a>
                    <ul class="accordion">
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Banks</span><span> Banks</span><span>3543759</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Exim Bank</span><span>Exim Bank</span><span>4754843</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>3437347</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Cash</span><span>Cash</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Mehedi Cashier</span><span>Mehedi Cashier</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Fuel Stock</span><span>Fuel Stock</span><span>3543759</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Octane</span><span>Octane</span><span>4754843</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>3437347</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                A/C Receivable</span><span>A/C Receivable</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Beximco</span><span>Beximco</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                        Equity</span><span>Equity</span><span>893595</span></a>
                    <ul class="accordion">
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Earnigs</span><span>Equity Earnigs</span><span>426264</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Bohddarhat</span><span>Bohddarhat</span><span>4754843</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>3437347</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Noakhali</span><span>Noakhali</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Shahriar (Manager)</span><span>Shahriar (Manager)</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                        Liabilities</span><span>Liabilities</span><span>74578245</span></a>
                    <ul class="accordion">
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Vendors</span><span>Vendors</span><span>5346663</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Banani</span><span>Banani</span><span>23545687</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>3437347</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                A/C Payables</span><span>A/C Payables</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Shahriar (Manager)</span><span>Shahriar (Manager)</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Loans</span><span>Loans</span><span>3543759</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Bohddarhat</span><span>Bohddarhat</span><span>4754843</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>3437347</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Noakhali</span><span>Noakhali</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Shahriar (Manager)</span><span>Shahriar (Manager)</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                        Expenses</span><span>Expenses</span><span>57634572</span></a>
                    <ul class="accordion">
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Inventory</span><span>Inventory</span><span>626724</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Jessore</span><span>Jessore</span><span>345784</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>78484</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Salary</span><span>Salary</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Shahriar (Manager)</span><span>Shahriar (Manager)</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                        Assets</span><span>Assets</span><span>8364983</span></a>
                    <ul class="accordion">
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Loans</span><span>Loans</span><span>3543759</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Bohddarhat</span><span>Bohddarhat</span><span>4754843</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>3437347</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Noakhali</span><span>Noakhali</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Shahriar (Manager)</span><span>Shahriar (Manager)</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Loans</span><span>Loans</span><span>3543759</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Bohddarhat</span><span>Bohddarhat</span><span>4754843</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>3437347</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Noakhali</span><span>Noakhali</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Shahriar (Manager)</span><span>Shahriar (Manager)</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                        Equity</span><span>Equity</span><span>893595</span></a>
                    <ul class="accordion">
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Loans</span><span>Loans</span><span>426264</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Bohddarhat</span><span>Bohddarhat</span><span>4754843</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>3437347</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Noakhali</span><span>Noakhali</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Shahriar (Manager)</span><span>Shahriar (Manager)</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                        Liabilities</span><span>Liabilities</span><span>74578245</span></a>
                    <ul class="accordion">
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Vendors</span><span>Vendors</span><span>5346663</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Banani</span><span>Banani</span><span>23545687</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>3437347</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                A/C Payables</span><span>A/C Payables</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Shahriar (Manager)</span><span>Shahriar (Manager)</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Loans</span><span>Loans</span><span>3543759</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Bohddarhat</span><span>Bohddarhat</span><span>4754843</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>3437347</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Noakhali</span><span>Noakhali</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Shahriar (Manager)</span><span>Shahriar (Manager)</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                        Expenses</span><span>Expenses</span><span>57634572</span></a>
                    <ul class="accordion">
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Inventory</span><span>Inventory</span><span>626724</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Jessore</span><span>Jessore</span><span>345784</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>78484</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg" alt=""/>
                                Salary</span><span>Salary</span><span>836854</span></a>
                            <ul class="accordion">
                                <li>
                                    <a href="#" class="accordion-btn"><span><img src="/images/arrow-svg.svg"
                                                                                 alt=""/>
                                        Shahriar (Manager)</span><span>Shahriar (Manager)</span><span>47423456</span></a>
                                    <ul class="accordion">
                                        <li>
                                            <a href="./table.html"
                                               target="_blank"><span>text</span><span>desc</span><span>4743467</span></a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import ApiService from "../../Services/ApiService";
import ApiRoutes from "../../Services/ApiRoutes";

export default {
    data() {
        return {
            popup: null,
            accHeads: []
        }
    },
    methods: {
        rightClick: function (e) {
            e.preventDefault();
            this.showPopup(e);
        },
        showPopup: function (evt) {
            this.popup.style.left = `${evt.pageX}px`;
            this.popup.style.top = `${evt.pageY}px`;
            this.popup.classList.add('active')
        },
        hidePopup: function (e) {
            if (e.target !== this.popup) {
                if (this.popup.classList.contains('active')) {
                    this.popup.classList.remove('active')
                }
            }
        },
        getAccountsHead: function () {
            ApiService.POST(ApiRoutes.AccountList, {}, res => {
                if (parseInt(res.status) === 200) {
                    this.accHeads = res.data;
                }
            });
        },
    },
    created() {
        this.getAccountsHead()
    },
    mounted() {
        const accordion = document.querySelector(".accordion");
        const accordionBtn = document.querySelectorAll(".accordion-btn");
        this.popup = document.querySelector(".popup-wrapper");
        const newAccount = document.getElementById('newAccount');
        const newAccForm = document.querySelector('.new-account-form-wrapper');
        const cancelBtn = document.querySelector('.cancel-btn');
        const form = document.querySelector('form');

        // accourdion show and hide
        accordionBtn.forEach((e) => {
            e.onclick = function (event) {
                event.preventDefault();
                e.nextElementSibling.classList.toggle("open");
                e.classList.toggle('active');
            }
        });


        // hide pup function
        const hidePopup = (e) => {

        };

        // hide the popup when user clicks outside the popup box
        window.addEventListener('click',  (e) => {
            this.hidePopup(e);
            if (e.target == newAccForm && e.target !== form) {
                newAccForm.classList.remove('active')
            }
        })

        // hide the popup when user spin the mouse wheel
        window.addEventListener('wheel',  (e) =>  {
            this.hidePopup(e)
        })


        newAccount.onclick = function (e) {
            hidePopup(e)
            if (!newAccForm.classList.contains('active')) {
                newAccForm.classList.add('active')
            }

        }

        cancelBtn.onclick = function () {
            newAccForm.classList.remove('active')
        }
    },
    destroyed() {

    }
}
</script>

<style scoped>

ul {
    list-style: none;
}

a {
    text-decoration: none;
    color: #000;
    font-weight: 400;
}


/* main content start here  */


/* popup area start  */
.popup-wrapper {
    display: block;
    position: absolute;
    background: #fff;
    box-shadow: 0px 0px 4px #00000047;
    z-index: 9;
    display: none;
}

.popup-wrapper.active {
    display: block;
}

.popup-wrapper ul li a {
    display: block;
    padding: 7px 25px 7px 15px;
    font-size: 14px;
    transition: all .3s;
}

.popup-wrapper ul li a:hover {
    color: #01987a;
}

/* popup area end */


/* new account area start  */
.new-account-form-wrapper {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 9;
    background: #00000070;
    justify-content: center;
    align-items: center;
    display: none;
}

.new-account-form-wrapper.active {
    display: flex;
}

.new-account-form-wrapper form {
    padding: 30px 20px;
    row-gap: 10px;
    background: #fff;
    border-radius: 5px;
    display: flex;
    flex-direction: column;
}

.new-account-form-wrapper form .input-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.new-account-form-wrapper form input,
textarea,
select {
    border: 1px solid #d2d2d2;
    outline: none;
    padding: 10px;
    width: 70%;
    border-radius: 4px;
}

.new-account-form-wrapper form label {
    margin-right: 10px;
}

.desc-label,
.notes-label {
    align-self: flex-start;
}

form .btn-wrapper {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    column-gap: 10px;
}

.btn-wrapper button {
    padding: 10px;
    border: none;
    background-color: red;
    color: #fff;
    cursor: pointer;

}

.btn-wrapper button:first-child {
    background-color: rgb(0, 140, 255);
    color: #fff;
    border: none;
    outline: none;
}

/* new account area end  */


.accordion-wrapper .accordion-heading-wrapper {
    display: flex;
    justify-content: space-between;
    border-bottom: 1px solid #d1d1d1;
    margin-bottom: 10px;
    padding: 5px;
}

.accordion-wrapper .accordion-heading-wrapper h4 {
    font-size: 18px;
    font-weight: 600;
    color: #a7a7a7;
}

.accordion-wrapper {
    width: 1000px;
}

.accordion-wrapper li a {
    display: flex;
    justify-content: space-between;
    padding: 5px;
    text-decoration: none;
    color: #000;
    font-size: 18px;
}

.accordion-wrapper ul {
    padding-left: 50px;
}

.accordion-wrapper .accordion-btn img {
    width: 10px;
    margin-right: 10px;
    transition: .4s ease;
}

.accordion {
    display: none;
}

.accordion.open {
    display: block;
}

.accordion-btn.active img {
    transform: rotate(90deg);
}

ul.accordion-wrapper a span:nth-child(2),
ul.accordion a span:nth-child(2) {
    position: absolute;
    left: 865px;
}

/* main content end here  */
</style>
