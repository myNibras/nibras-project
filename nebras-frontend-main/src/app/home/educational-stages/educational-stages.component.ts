import { AcademicLevelsService } from './../../shared/services/academic-levels/academic-levels.service';
import { Component, OnDestroy, OnInit } from '@angular/core';
import { AcademicLevelsResponse } from 'app/shared/models/academic-levels';
import { NgxSkeletonLoaderModule } from 'ngx-skeleton-loader';
import { Subject, takeUntil } from 'rxjs';
import { NgFor, NgIf } from '@angular/common';
import { StorageService } from 'app/core/storage/storage.service';
import { RouterLink } from '@angular/router';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-educational-stages',
  imports: [NgFor, NgIf, NgxSkeletonLoaderModule, RouterLink, TranslateModule],
  templateUrl: './educational-stages.component.html',
  styleUrl: './educational-stages.component.scss'
})
export class EducationalStagesComponent implements OnInit, OnDestroy {

  destroy$ = new Subject<void>;
  skeletonCount: number[] = [];

  constructor(
    private academicLevelsService: AcademicLevelsService,
    public storageService: StorageService,
  ) { }

  academicLevels!: AcademicLevelsResponse;
  loading = true;

  ngOnInit(): void {
    this.storageService.siteLanguage$.pipe(takeUntil(this.destroy$)).subscribe(() => {
      this.loading = true;
      this.academicLevelsService.get().subscribe((response: AcademicLevelsResponse) => {
        this.academicLevels = response;
        // set skeletons equal to data length (fallback 3 if empty)
        this.skeletonCount = Array(response.data.length || 3).fill(0);
        this.loading = false;
      });
    });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

}
