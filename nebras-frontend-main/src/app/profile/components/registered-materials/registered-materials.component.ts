import { Component, OnInit, OnDestroy } from '@angular/core';
import { AuthService } from 'app/shared/services/auth-service/auth-service.service';
import { PurchasedCourse } from 'app/shared/models/courses';
import { TranslatePipe, TranslateModule } from '@ngx-translate/core';
import { MaterialCardComponent } from "../material-card/material-card.component";
import { StorageService } from 'app/core/storage/storage.service';
import { Subject, switchMap, takeUntil, tap } from 'rxjs';

@Component({
  selector: 'app-registered-materials',
  imports: [TranslatePipe, TranslateModule, MaterialCardComponent],
  templateUrl: './registered-materials.component.html',
  styleUrl: './registered-materials.component.scss'
})
export class RegisteredMaterialsComponent implements OnInit, OnDestroy {

  materials: PurchasedCourse[] = [];
  loading = true;

  private destroy$ = new Subject<void>();

  constructor(
    private authService: AuthService,
    public storageService: StorageService
  ) { }

  ngOnInit(): void {
    this.storageService.siteLanguage$
      .pipe(
        takeUntil(this.destroy$),
        tap(() => (this.loading = true)),
        switchMap(() => this.authService.getRecordedMaterial())
      )
      .subscribe({
        next: (response) => {
          this.materials = response?.data?.length ? response.data : [];
          this.loading = false;
          console.log(this.materials)
        },
        error: (err) => {
          console.error('Error loading recorded materials:', err);
          this.materials = [];
          this.loading = false;
        }
      });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }


}
