import { Component, OnInit } from '@angular/core';
import { NgClass } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { TestimonialsService } from 'app/shared/services/testimonials/testimonials.service';
import { Testimonial } from 'app/shared/models/testimonial';

@Component({
  selector: 'app-my-testimonials',
  imports: [TranslateModule, NgClass],
  templateUrl: './my-testimonials.component.html',
  styleUrl: './my-testimonials.component.scss'
})
export class MyTestimonialsComponent implements OnInit {
  testimonials: Testimonial[] = [];
  loading = true;

  constructor(private testimonialsService: TestimonialsService) {}

  ngOnInit(): void {
    this.testimonialsService.getMyTestimonials().subscribe({
      next: (list) => {
        this.testimonials = list;
        this.loading = false;
      },
      error: () => {
        this.testimonials = [];
        this.loading = false;
      }
    });
  }

  getStatusLabel(status: string): string {
    switch (status) {
      case 'approved': return 'Accepted';
      case 'rejected': return 'Rejected';
      case 'pending': return 'Pending';
      default: return status;
    }
  }

  getStatusClasses(status: string): string {
    switch (status) {
      case 'approved':
        return 'rounded-md border border-[#A3E1A8] bg-[#ECFFED] px-3 py-2 text-sm font-medium text-green-700';
      case 'rejected':
        return 'rounded-md border border-[#F5C2C2] bg-[#FFEEEE] px-3 py-2 text-sm font-medium text-red-700';
      case 'pending':
        return 'rounded-md border border-[#F5E6C2] bg-[#FFF8EE] px-3 py-2 text-sm font-medium text-amber-700';
      default:
        return 'rounded-md border border-[#E6EEF6] bg-[#F5F7FA] px-3 py-2 text-sm font-medium text-[#545F71]';
    }
  }

  formatDate(dateStr: string): string {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString(undefined, {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false
    });
  }
}
