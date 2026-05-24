import { Component, ElementRef, HostListener, OnInit, Input, Output, EventEmitter } from '@angular/core';
import { NgIf, NgFor, NgClass } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { TranslateModule } from '@ngx-translate/core';
import { Student } from 'app/shared/models/auth';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { ClassesService } from 'app/shared/services/classes/classes.service';
import { Grade } from 'app/shared/models/classes';
import { HttpClient } from '@angular/common/http';
import { TranslateService } from '@ngx-translate/core';
import { StorageService } from 'app/core/storage/storage.service';
import { takeUntil } from 'rxjs';
import { Subject } from 'rxjs';
import { strictEmailValidator } from 'app/shared/validators/email.validator';


// ----- Models -----
type DropdownKey = 'class' | 'age' | 'gender' | 'country';

interface Option<T = string> {
  label: string;
  value: T;
  flag?: string;
}

interface DropdownState<T = any> {
  open: boolean;
  highlighted: number;
  placeholder: string;
  options: Option<T>[];
  search?: string;
}


@Component({
  selector: 'app-user-info',
  standalone: true,
  imports: [NgIf, NgFor, ReactiveFormsModule, NgClass, TranslateModule],
  templateUrl: './user-info.component.html',
  styleUrls: ['./user-info.component.scss'],
})
export class UserInfoComponent implements OnInit {
  @Input() student!: Student;
  @Output() changePassword = new EventEmitter<void>();
  @Output() profileUpdated = new EventEmitter<boolean>();

  onChangePasswordClick() {
    this.changePassword.emit();
  }
  // ---------- Form ----------
  isEditing = false;
  form: FormGroup;
  private snapshotValue!: any;
  previewImageUrl: string | null = null;
  selectedFile: File | null = null;
  imageError: string | null = null;

  constructor(
    private el: ElementRef<HTMLElement>,
    private fb: FormBuilder,
    private authService: AuthService,
    private classesService: ClassesService,
    public storageService: StorageService,
    private http: HttpClient,
    private translate: TranslateService
  ) {
    this.form = this.fb.group({
      fullName: ['', [Validators.required, Validators.minLength(2), Validators.maxLength(50)]],
      class: [null, Validators.required],
      age: [null, [Validators.required, Validators.min(3)]],
      gender: [null, Validators.required],
      email: ['', [Validators.required, strictEmailValidator()]],
      phone: ['', [Validators.required, Validators.pattern(/^\d{7,15}$/)]],
      phoneCountry: ['QA', Validators.required],
      passwordPlaceholder: [{ value: '••••••••', disabled: true }],
    });

  }
  showChangePassword = false;

  openChangePassword() {
    this.showChangePassword = true;
  }

  closeChangePassword() {
    this.showChangePassword = false;
  }

  ngOnInit(): void {
    this.http.get<any[]>('app/assets/json/countries.json').subscribe({
      next: (countries) => {
        this.COUNTRIES = countries.map(c => ({
          code: c.country_code,
          name: c.country_name_english,
          arName: c.country_name_arabic,
          flag: c.flag,
          dialCode: c.phone_code
        }));

        this.initializeCountryDropdown();
      },
      error: (err) => console.error('Failed to load countries JSON:', err)
    });
    this.storageService.siteLanguage$.pipe(takeUntil(this.destroy$)).subscribe(() => {
      if (this.COUNTRIES.length) {
        this.initializeCountryDropdown();
      }
    });

      if(this.student){
        this.form.patchValue({
          fullName: this.student.name,
          class: this.student.class_id,
          age: this.student.age,
          gender: String(this.student.gender),
          email: this.student.email,
          phone: this.student.phone,
          phoneCountry: this.student.country
        });
        if(this.student.profile_picture){
          this.previewImageUrl = this.student.profile_picture;
        }
      }
    // Load classes from API
    this.classesService.get().subscribe({
      next: (grades: Grade[]) => {
        this.dropdowns.class.options = grades.map(g => ({
          label: g.name,
          value: g.id
        }));
      },
      error: (err) => {
        console.error('Error loading classes:', err);
      }
    });
  }

  private isArabic(): boolean {
    const currentLang = this.translate.currentLang;
    const storedLang = this.storageService['siteLanguage$']['_value'] || currentLang;
    return storedLang === 'ar' || currentLang === 'ar';
  }


  private destroy$ = new Subject<void>();

  COUNTRIES: {
    code: string;
    name: string;
    arName: string;
    flag: string;
    dialCode: string;
  }[] = [];

  private initializeCountryDropdown() {
    this.dropdowns.country.options = this.COUNTRIES.map(c => ({
      label: this.isArabic() ? c.arName : c.name,
      value: c.code,
      flag: c.flag,
    }));

    // Set the student's country or default to QA
    const userCountry = this.form.get('phoneCountry')?.value || 'QA';
    const foundIndex = this.dropdowns.country.options.findIndex(o => o.value === userCountry);
    this.dropdowns.country.highlighted = foundIndex >= 0 ? foundIndex : 0;
  }


  private ageOptions: Option<number>[] = Array.from({ length: 13 }, (_, i) => {
    const v = i + 6; // 6..18
    return { label: String(v), value: v };
  });

  private genderOptions: Option[] = [
    { label: 'Male', value: '0' },
    { label: 'Female', value: '1' },
  ];

  private countryOptions: Option[] = this.COUNTRIES.map(c => ({
    label: c.name,
    value: c.code,
    flag: c.flag,
  }));

  // ---------- Generic dropdown state ----------
  dropdowns: Record<DropdownKey, DropdownState> = {
    class: { open: false, highlighted: -1, placeholder: 'Select class', options: [] },
    age: { open: false, highlighted: -1, placeholder: 'Select age', options: this.ageOptions },
    gender: { open: false, highlighted: -1, placeholder: 'Select', options: this.genderOptions },
    country: { open: false, highlighted: -1, placeholder: 'Country', options: this.countryOptions, search: '' },
  };

  onHostClick() {
    this.closeAll();
  }

  // ---------- Helpers to read current selections ----------
  getLabel(name: DropdownKey): string {
    const ctrl = this.form.get(name === 'country' ? 'phoneCountry' : name)!;
    const val = ctrl.value;
    const opt = this.filtered(name).find(o => o.value === val) || this.dropdowns[name].options.find(o => o.value === val);
    return opt?.label ?? this.dropdowns[name].placeholder;
  }

  get countryFlag(): string {
    const code = this.form.get('phoneCountry')?.value as string;
    return this.COUNTRIES.find(c => c.code === code)?.flag ?? 'https://flagcdn.com/w40/qa.png';
  }

  get phonePlaceholder(): string {
    const code = this.form.get('phoneCountry')?.value as string;
    const dial = this.COUNTRIES.find(c => c.code === code)?.dialCode ?? '974';
    return `+${dial}XXXXXXXX`;
  }

  // ---------- Generic dropdown actions ----------
  toggle(name: DropdownKey) {
    if (!this.isEditing) return;
    this.closeAll(name);
    this.dropdowns[name].open = !this.dropdowns[name].open;
    if (this.dropdowns[name].open) {
      const ctrl = this.form.get(name === 'country' ? 'phoneCountry' : name)!;
      const list = this.filtered(name);
      const idx = list.findIndex(o => o.value === ctrl.value);
      this.dropdowns[name].highlighted = idx >= 0 ? idx : 0;
    }
  }

  select(name: DropdownKey, i: number) {
    if (!this.isEditing) return;
    const list = this.filtered(name);
    const chosen = list[i] ?? list[0];
    const ctrl = this.form.get(name === 'country' ? 'phoneCountry' : name)!;
    ctrl.setValue(chosen.value);
    this.dropdowns[name].open = false;
    this.dropdowns[name].highlighted = i;
  }

  onCountrySearch(event: Event) {
    this.dropdowns.country.search = (event.target as HTMLInputElement).value;
  }

  onKeydown(name: DropdownKey, event: KeyboardEvent) {
    if (!this.isEditing) return;
    const dd = this.dropdowns[name];
    if (!dd.open && (event.key === 'Enter' || event.key === ' ')) {
      event.preventDefault();
      this.toggle(name);
      return;
    }
    if (!dd.open) return;

    const list = this.filtered(name);
    switch (event.key) {
      case 'ArrowDown':
        event.preventDefault();
        dd.highlighted = (dd.highlighted + 1) % list.length;
        break;
      case 'ArrowUp':
        event.preventDefault();
        dd.highlighted = (dd.highlighted - 1 + list.length) % list.length;
        break;
      case 'Enter':
      case ' ':
        event.preventDefault();
        this.select(name, Math.max(0, dd.highlighted));
        break;
      case 'Escape':
        dd.open = false;
        break;
    }
  }

  filtered(name: DropdownKey): Option[] {
    if (name !== 'country') return this.dropdowns[name].options;

    const q = (this.dropdowns.country.search ?? '').trim().toLowerCase();
    if (!q) return this.dropdowns.country.options;

    return this.dropdowns.country.options.filter(o =>
      o.label.toLowerCase().includes(q) || String(o.value).toLowerCase().includes(q)
    );
  }


  // ---------- Edit/save/cancel ----------
  startEdit() {
    if (this.isEditing) return;
    this.isEditing = true;
    this.snapshotValue = this.form.getRawValue();
  }

  cancelEdit() {
    if (!this.isEditing) return;
    this.isEditing = false;
    this.form.reset(this.snapshotValue);
    this.previewImageUrl = null; // Clear preview image on cancel
    this.selectedFile = null; // Clear selected file on cancel
    this.imageError = null; // Clear image error on cancel
    this.closeAll();
  }

  save() {
    if (this.form.invalid) {
      this.logFormErrors(this.form);
      this.form.markAllAsTouched();
      return;
    }
    this.imageError = '';
    // If there's a selected file, use FormData, otherwise use JSON
    if (this.selectedFile) {
      const formData = new FormData();
      formData.append('name', this.form.value.fullName);
      formData.append('class_id', String(Number(this.form.value.class)));
      formData.append('age', String(Number(this.form.value.age)));
      formData.append('gender', String(Number(this.form.value.gender)));
      formData.append('email', this.form.value.email);
      formData.append('phone', this.form.value.phone);
      formData.append('country', this.form.value.phoneCountry);
      formData.append('profile_picture', this.selectedFile);

      this.authService.updateProfile(formData).subscribe({
        next: (student) => {
          this.student = student;
          this.isEditing = false;
          this.previewImageUrl = null; // Clear preview after successful upload
          this.selectedFile = null; // Clear selected file
          this.closeAll();
          this.profileUpdated.emit(true); // Emit event to parent
        },
        error: (err) => {
          console.error('Failed to update profile:', err);
        }
      });
    } else {
      const payload = {
        name: this.form.value.fullName,
        class_id: Number(this.form.value.class),
        age: Number(this.form.value.age),
        gender: Number(this.form.value.gender),
        email: this.form.value.email,
        phone: this.form.value.phone,
        country: this.form.value.phoneCountry
      };

      this.authService.updateProfile(payload).subscribe({
        next: (student) => {
          this.student = student;
          this.isEditing = false;
          this.closeAll();
          this.profileUpdated.emit(true); // Emit event to parent
        },
        error: (err) => {
          console.error('Failed to update profile:', err);
        }
      });
    }
  }

  logFormErrors(form: FormGroup) {
    Object.keys(form.controls).forEach(controlName => {
      const control = form.get(controlName);
  
      if (control && control.invalid) {
        // console.group(`❌ Control: ${controlName}`);
        // console.log('Errors:', control.errors);
        // console.log('Value:', control.value);
        // console.log('Touched:', control.touched);
        // console.log('Dirty:', control.dirty);
        // console.groupEnd();
      }
    });
  }

  // ---------- Utilities ----------
  private closeAll(except?: DropdownKey) {
    (Object.keys(this.dropdowns) as DropdownKey[]).forEach(k => {
      if (k !== except) this.dropdowns[k].open = false;
    });
  }
  get selectedDialCode(): string {
    const code = this.form.get('phoneCountry')?.value as string;
    return this.COUNTRIES.find(c => c.code === code)?.dialCode ?? '';
  }

  get profileImage(): string {
    // Priority: preview image > student profile_picture > default avatar
    if (this.previewImageUrl) {
      return this.previewImageUrl;
    }
    if (this.student?.profile_picture) {
      return this.student.profile_picture;
    }
    // Default profile image - using a placeholder or generate one based on name
    const name = this.student?.name || 'User';
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=F2F4F7&color=667085&size=120`;
  }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    
    // Clear previous error
    this.imageError = null;
    
    if (!file) {
      return;
    }

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
      this.imageError = this.translate.instant('Please select a valid image file (JPEG, JPG, or PNG)');
      input.value = ''; // Reset input
      return;
    }

    // Validate file size (10MB = 10 * 1024 * 1024 bytes)
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    if (file.size > maxSize) {
      this.imageError = this.translate.instant('File size must be less than 10MB');
      input.value = ''; // Reset input
      return;
    }

    // File is valid, create preview and store file
    this.selectedFile = file;
    this.imageError = null; // Clear any previous errors
    const reader = new FileReader();
    reader.onload = (e: ProgressEvent<FileReader>) => {
      if (e.target?.result) {
        this.previewImageUrl = e.target.result as string;
      }
    };
    reader.readAsDataURL(file);
  }

  @HostListener('document:click', ['$event'])
  onDocumentClick(ev: MouseEvent) {
    if (!this.el.nativeElement.contains(ev.target as Node)) {
      this.closeAll();
    }
  }

}
