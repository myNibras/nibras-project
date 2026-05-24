import { ComponentFixture, TestBed } from '@angular/core/testing';

import { RelatedCoursesComponent } from './related-courses.component';

describe('RelatedCoursesComponent', () => {
  let component: RelatedCoursesComponent;
  let fixture: ComponentFixture<RelatedCoursesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RelatedCoursesComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(RelatedCoursesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
