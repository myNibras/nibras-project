import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StudentSubjectHeroComponent } from './student-subject-hero.component';

describe('StudentSubjectHeroComponent', () => {
  let component: StudentSubjectHeroComponent;
  let fixture: ComponentFixture<StudentSubjectHeroComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [StudentSubjectHeroComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StudentSubjectHeroComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
